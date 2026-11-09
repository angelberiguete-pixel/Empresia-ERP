<?php
/* Empresia Audit — GPL-3.0-or-later */
require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

class modEmpresiaAudit extends DolibarrModules
{
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 590123; // Provisional; confirm uniqueness before activation.
        $this->rights_class = 'empresiaaudit';
        $this->family = 'crm';
        $this->module_position = '89';
        $this->name = 'EmpresiaAudit';
        $this->description = 'Empresia: authenticated diagnostic and audit metrics';
        $this->version = '0.1.0';
        $this->const_name = 'MAIN_MODULE_EMPRESIAAUDIT';
        $this->picto = 'chart';
        $this->module_parts = array();
        $this->dirs = array();
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array();
        $this->phpmin = array(8, 0);
        $this->need_dolibarr_version = array(19, 0);
        $this->const = array();
        $this->tabs = array();
        $this->rights = array();
        $this->rights[0] = array(59012301, 'Read business diagnostics', 'Read diagnostics', 0, 'audit', 'read');
        $this->rights[1] = array(59012302, 'Create business diagnostics', 'Create diagnostics', 0, 'audit', 'write');
        $this->menu = array(
            array('fk_menu' => '', 'type' => 'top', 'titre' => 'Empresia Audit', 'mainmenu' => 'empresiaaudit', 'leftmenu' => '', 'url' => '/empresiaaudit/index.php', 'langs' => '', 'position' => 200, 'enabled' => 'isModEnabled("empresiaaudit")', 'perms' => '$user->hasRight("empresiaaudit", "audit", "read")', 'target' => '', 'user' => 2)
        );
    }

    public function init($options = '')
    {
        return $this->_init(array(), $options);
    }

    public function remove($options = '')
    {
        return $this->_remove(array(), $options);
    }
}
