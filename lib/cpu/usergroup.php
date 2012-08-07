<?php
import('.../lib/cpu/lib.php');

class cpu_usergroup extends cpu_lib {
    var $type = 'usergroup';
    
    var $db=false;
    function cpu_usergroup() {$this->db = load_db('usergroup');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
    );
    
    
    
    function validate($group,$new=false) {
        if (!$group = parent::validate($group,false)) return false;
        
        if ($new) {
            $search = $this->search($group['name'],'name');
            if (!empty($search)) return $this->error($this->type,'dupname');
        }
        
        return $group;
    }
    
    
    function get_permissions() {
        return $this->db->permissions;
    }
}
