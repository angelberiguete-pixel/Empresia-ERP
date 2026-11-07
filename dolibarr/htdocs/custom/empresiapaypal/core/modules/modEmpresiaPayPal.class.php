<?php
/* Empresia PayPal reconciliation module.
 * GPL-3.0-or-later. Does not change upstream Dolibarr payment files.
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modEmpresiaPayPal extends DolibarrModules
{
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 115990;
        $this->rights_class = 'empresiapaypal';
        $this->family = 'financial';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'Verified CRM PayPal capture reconciliation inbox (no automatic accounting postings)';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_EMPRESIAPAYPAL';
        $this->picto = 'payment';
        $this->dirs = array();
        $this->config_page_url = array();
        $this->hidden = false;
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(7, 4);
        $this->need_dolibarr_version = array(19, 0);
        $this->langfiles = array();
        $this->const = array();
        $this->tabs = array();
        $this->boxes = array();
        $this->rights = array();
        $this->menu = array();
    }

    public function init($options = '')
    {
        $sql = array(
            'CREATE TABLE IF NOT EXISTS '.MAIN_DB_PREFIX.'empresia_paypal_inbox ('.
            'paypal_capture_id VARCHAR(64) PRIMARY KEY, '.
            'entity INTEGER NOT NULL, '.
            'crm_tenant_id VARCHAR(36) NOT NULL, '.
            'crm_order_id VARCHAR(36) NOT NULL, '.
            'paypal_order_id VARCHAR(64) NOT NULL, '.
            'amount_minor BIGINT NOT NULL, '.
            'currency VARCHAR(3) NOT NULL, '.
            'payload_hash CHAR(64) NOT NULL, '.
            'payload_json TEXT NOT NULL, '.
            'status VARCHAR(32) NOT NULL DEFAULT \'pending_mapping\', '.
            'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'.
            ')'
        );
        return $this->_init($sql, $options);
    }

    public function remove($options = '')
    {
        // Leave reconciliation records intact if the module is deactivated.
        return $this->_remove(array(), $options);
    }
}
