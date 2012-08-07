<?php
import('.../lib/cpu/lib.php');

class cpu_user extends cpu_lib {
    var $type = 'user';
    
    var $db=false;
    function cpu_user() {$this->db = load_db('user');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'password'=>4,
        'group'=>true,
    );
    
    
    
    function validate($user,$new=false) {
        if (!$user = parent::validate($user,false)) return false;
        
        $authcpu = load_cpu('auth');
        $search = $this->search($user['name'],'name');
        if ($new) {
            if (!empty($search)) return $this->error($this->type,'dup_name');
            
            $user['password'] = $authcpu->encrypt($user['password']);
        } else {
            // Check for renaming into an already taken name
            while ($new = array_pop($search)) {
                if ($user['id'] != $new['id']) return $this->error($this->type,'dup_name');
            }
            
            // Encrypt password or reset it
            if ($user['password'] == '[no_change]') {
                unset($user['password']);
            } else $user['password'] = $authcpu->encrypt($user['password']);
        }
        
        
        return $user;
    }
    
}
?>