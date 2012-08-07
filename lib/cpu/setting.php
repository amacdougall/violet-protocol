<?php
import('.../lib/cpu/lib.php');

class cpu_setting extends cpu_lib {
    var $type = 'setting';
    
    var $db=false;
    function cpu_setting() {$this->db = load_db('setting');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'type'=>3,
    );
    
    
    
    function editall($settings) {
        $this->db->saveall($settings);
        $this->good('config','settings_good');
        return true;
    }
    
}
?>