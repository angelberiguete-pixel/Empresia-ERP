<?php
/* Empresia Audit — GPL-3.0-or-later. Internal, authenticated Dolibarr page. */
$res = 0;
if (!$res && file_exists('../main.inc.php')) $res = include '../main.inc.php';
if (!$res && file_exists('../../main.inc.php')) $res = include '../../main.inc.php';
if (!$res) die('Dolibarr environment unavailable');

/** @var DoliDB $db */
/** @var User $user */
/** @var Conf $conf */
if (!$user->hasRight('empresiaaudit', 'audit', 'read')) accessforbidden();

$entity = (int) $conf->entity;
$action = GETPOST('action', 'aZ09');
$error = '';
$dimensions = array('strategy' => 'Estrategia', 'sales' => 'Ventas', 'processes' => 'Procesos', 'technology' => 'Tecnología', 'people' => 'Equipo');

/* Actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    if (!$user->hasRight('empresiaaudit', 'audit', 'write')) accessforbidden();
    $name = trim(GETPOST('name', 'restricthtml'));
    $source = trim(GETPOST('source', 'alpha'));
    $campaign = trim(GETPOST('utm_campaign', 'restricthtml'));
    $allowedSources = array('direct', 'instagram', 'facebook', 'google', 'referral', 'visit');
    if ($name === '' || dol_strlen($name) > 190 || dol_strlen($campaign) > 120 || !in_array($source, $allowedSources, true)) {
        $error = 'Invalid diagnostic metadata.';
    }
    $values = array();
    foreach ($dimensions as $key => $label) {
        $raw = GETPOST($key, 'alpha');
        if (!preg_match('/^[0-5]$/', (string) $raw)) $error = 'Dimension scores must be from 0 to 5.';
        else $values[$key] = (int) $raw;
    }
    if (!$error) {
        $score = (int) (array_sum($values) * 4);
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'empresiaaudit_assessment (entity, name, source, utm_campaign, strategy, sales, processes, technology, people, score, fk_user_create, date_creation) VALUES (';
        $sql .= $entity.', \''.$db->escape($name).'\', \''.$db->escape($source).'\', '.($campaign === '' ? 'NULL' : "'".$db->escape($campaign)."'").', ';
        $sql .= implode(', ', array($values['strategy'], $values['sales'], $values['processes'], $values['technology'], $values['people']));
        $sql .= ', '.$score.', '.(int) $user->id.', \''.$db->idate(dol_now()).'\')';
        if ($db->query($sql)) {
            header('Location: '.$_SERVER['PHP_SELF'].'?saved=1');
            exit;
        }
        $error = 'Database write failed. Verify the module was installed and check logs.';
    }
}

/* Views */
llxHeader('', 'Empresia — Auditoría empresarial');
print '<h1>Empresia · Diagnóstico empresarial</h1>';
print '<p>Evaluación interna de madurez (0–100). No es una auditoría certificada ni una recomendación producida por IA.</p>';
if (GETPOST('saved', 'int')) print '<div class="ok">Diagnóstico guardado.</div>';
if ($error) print '<div class="error">'.dol_escape_htmltag($error).'</div>';
if ($user->hasRight('empresiaaudit', 'audit', 'write')) {
    print '<form method="post" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="create">';
    print '<fieldset><legend>Nuevo diagnóstico</legend>';
    print '<p><label>Nombre comercial de prueba <input name="name" maxlength="190" required></label></p>';
    print '<p><label>Origen <select name="source"><option value="direct">Directo</option><option value="instagram">Instagram</option><option value="facebook">Facebook</option><option value="google">Google</option><option value="referral">Referido</option><option value="visit">Visita</option></select></label></p>';
    print '<p><label>Campaña (opcional) <input name="utm_campaign" maxlength="120"></label></p>';
    foreach ($dimensions as $key => $label) {
        print '<p><label>'.dol_escape_htmltag($label).' (0–5) <select name="'.$key.'">';
        for ($i=0; $i<=5; $i++) print '<option value="'.$i.'">'.$i.'</option>';
        print '</select></label></p>';
    }
    print '<button type="submit" class="button">Guardar diagnóstico</button></fieldset></form>';
}
$resql = $db->query('SELECT COUNT(rowid) AS total, COALESCE(AVG(score),0) AS average_score FROM '.MAIN_DB_PREFIX.'empresiaaudit_assessment WHERE entity = '.$entity);
if ($resql) {
    $stats = $db->fetch_object($resql);
    print '<h2>Indicadores</h2><p>Diagnósticos: <strong>'.(int)$stats->total.'</strong> | Puntuación media: <strong>'.round((float)$stats->average_score,1).'/100</strong></p>';
}
$resql = $db->query('SELECT rowid,name,source,score,date_creation FROM '.MAIN_DB_PREFIX.'empresiaaudit_assessment WHERE entity = '.$entity.' ORDER BY rowid DESC LIMIT 50');
if ($resql) {
    print '<h2>Últimos 50 registros</h2><table class="noborder centpercent"><tr class="liste_titre"><th>ID</th><th>Negocio</th><th>Origen</th><th>Índice</th><th>Fecha</th></tr>';
    while ($r = $db->fetch_object($resql)) {
        print '<tr><td>'.(int)$r->rowid.'</td><td>'.dol_escape_htmltag($r->name).'</td><td>'.dol_escape_htmltag($r->source).'</td><td>'.(int)$r->score.'/100</td><td>'.dol_escape_htmltag($r->date_creation).'</td></tr>';
    }
    print '</table>';
}
$resql = $db->query('SELECT source,COUNT(rowid) AS total,AVG(score) AS avg_score FROM '.MAIN_DB_PREFIX.'empresiaaudit_assessment WHERE entity = '.$entity.' GROUP BY source ORDER BY total DESC');
if ($resql) {
    print '<h2>Indicadores por origen</h2><table class="noborder centpercent"><tr class="liste_titre"><th>Canal</th><th>Cantidad</th><th>Media</th></tr>';
    while ($r = $db->fetch_object($resql)) {
        print '<tr><td>'.dol_escape_htmltag($r->source).'</td><td>'.(int)$r->total.'</td><td>'.round((float)$r->avg_score,1).'/100</td></tr>';
    }
    print '</table>';
}
llxFooter();
$db->close();
