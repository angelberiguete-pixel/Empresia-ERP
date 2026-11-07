<?php
/* Empresia PayPal CRM relay receiver.
 * Receives only CRM-confirmed captures, NOT PayPal public webhooks.
 * Records an idempotent reconciliation inbox. Does NOT credit ERP invoices
 * until a trusted Dolibarr invoice mapping and separate accounting workflow exist.
 * License GPL-3.0-or-later.
 */
define('NOLOGIN', 1);
define('NOCSRFCHECK', 1);
define('NOTOKENRENEWAL', 1);
define('NOREQUIREMENU', 1);
define('NOREQUIREHTML', 1);
require __DIR__.'/../../main.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function empresiaReply($status, $payload)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    empresiaReply(405, array('error' => 'Method not allowed'));
}
$secret = getenv('EMPRESIA_PAYPAL_BRIDGE_SECRET');
$tenant = getenv('EMPRESIA_PAYPAL_TENANT_ID');
$entity = getenv('EMPRESIA_PAYPAL_ENTITY');
if (!$secret || strlen($secret) < 32 || !$tenant || !$entity || !ctype_digit((string) $entity)) {
    empresiaReply(503, array('error' => 'Relay not configured'));
}

$raw = file_get_contents('php://input', false, null, 0, 100001);
if (!is_string($raw) || strlen($raw) > 100000) {
    empresiaReply(413, array('error' => 'Payload too large'));
}
$timestamp = $_SERVER['HTTP_X_EMPRESIA_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_EMPRESIA_SIGNATURE'] ?? '';
if (!ctype_digit((string) $timestamp) ||
    abs(time() - (int) $timestamp) > 300 ||
    !preg_match('/^sha256=[a-f0-9]{64}$/', $signature)) {
    empresiaReply(401, array('error' => 'Invalid signature headers'));
}
$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$raw, $secret);
if (!hash_equals($expected, $signature)) {
    empresiaReply(401, array('error' => 'Signature mismatch'));
}

$data = json_decode($raw, true);
if (!is_array($data) ||
    ($data['version'] ?? null) !== 1 ||
    ($data['event_type'] ?? '') !== 'paypal.payment.completed' ||
    ($data['tenant_id'] ?? '') !== $tenant ||
    !preg_match('/^[a-f0-9-]{36}$/i', (string) ($data['crm_order_id'] ?? '')) ||
    !preg_match('/^[A-Za-z0-9]{6,64}$/', (string) ($data['paypal_order_id'] ?? '')) ||
    !preg_match('/^[A-Za-z0-9]{6,64}$/', (string) ($data['paypal_capture_id'] ?? '')) ||
    !in_array(($data['currency'] ?? ''), array('USD','EUR','GBP','CAD','AUD'), true) ||
    !is_int($data['amount_minor'] ?? null) ||
    $data['amount_minor'] < 1 ||
    $data['amount_minor'] > 10000000) {
    empresiaReply(422, array('error' => 'Invalid signed event'));
}

global $db;
$capture = (string) $data['paypal_capture_id'];
$payloadHash = hash('sha256', $raw);
$table = MAIN_DB_PREFIX.'empresia_paypal_inbox';
$sql = 'SELECT payload_hash FROM '.$table." WHERE paypal_capture_id='".$db->escape($capture)."'";
$res = $db->query($sql);
if (!$res) {
    empresiaReply(503, array('error' => 'ERP reconciliation table missing'));
}
if ($db->num_rows($res)) {
    $record = $db->fetch_object($res);
    if (!hash_equals((string) $record->payload_hash, $payloadHash)) {
        empresiaReply(409, array('error' => 'Conflicting capture payload'));
    }
    empresiaReply(200, array('accepted' => true, 'duplicate' => true));
}

$insert = 'INSERT INTO '.$table.' (paypal_capture_id, entity, crm_tenant_id, crm_order_id, paypal_order_id, amount_minor, currency, payload_hash, payload_json, status, created_at) VALUES ('
    ."'".$db->escape($capture)."',"
    .(int) $entity.','
    ."'".$db->escape((string) $tenant)."',"
    ."'".$db->escape((string) $data['crm_order_id'])."',"
    ."'".$db->escape((string) $data['paypal_order_id'])."',"
    .(int) $data['amount_minor'].','
    ."'".$db->escape((string) $data['currency'])."',"
    ."'".$db->escape($payloadHash)."',"
    ."'".$db->escape($raw)."',"
    ."'pending_mapping', CURRENT_TIMESTAMP)";
if (!$db->query($insert)) {
    // Concurrency-safe: if a concurrent request already inserted this
    // exact capture, acknowledge only if its payload matches.
    $again = $db->query($sql);
    if ($again && $db->num_rows($again)) {
        $record = $db->fetch_object($again);
        if (hash_equals((string) $record->payload_hash, $payloadHash)) {
            empresiaReply(200, array('accepted' => true, 'duplicate' => true));
        }
    }
    empresiaReply(503, array('error' => 'ERP persistence failed'));
}
empresiaReply(200, array('accepted' => true, 'duplicate' => false));
