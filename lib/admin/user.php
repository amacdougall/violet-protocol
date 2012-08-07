<?php
import('.../lib/admin/config.php');
class admin_user extends admin_config {
    // Set variables for user subsection
    var $navigation = array(
        'user:main'=>false,
        'user:add'=>'user:main',
        'user:edit'=>'user:edit|user:main',
    );
    var $lang = 'user';
    var $subsection = 'user';
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields() {
        return array(
            'name'=>'text',
            'password'=>'password_v',
            'email'=>'text',
            'group'=>new item_list('usergroup'),
        );
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Validate and retreive requested user
    ////////////////////////////////////////////////////////////
    function v_userid(&$userid,$data=array()) {
        if (!setvar($userid,'g','user')) return $this->warning('usernoid');
        
        if (!$user = $this->v_batch('user',$userid)) return $this->error('userbadid');
        
        if ($data) return $this->cpu('user','format',$data,$user);
        return $user;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // User main page
    ////////////////////////////////////////////////////////////
    function d_main() {
        $this->newpage('user:main');
        
        // Get user info
        if (!$userlist = plaintext($this->cpu('user','get'))) return false;
        if (!$usergroups = plaintext($this->cpu('usergroup','get'))) return false;
        
        // Sort users by usergroup
        foreach($usergroups as $group) {
            // Create list for each usergroup
            $this->addline('<h2>' . $group['name'] . '</h2>');
            $list = new admin_editlist;
            foreach ($userlist as $user) {
                if ($user['group'] == $group['id']) {
                    $list->add(array(
                        'id'=>$user['id'],
                        'url'=>'?p=config:user:edit&amp;user='.$user['id'],
                        'title'=>$user['name'],
                    ));
                }
            }
            // Display list
            $this->addline($list->render());
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add user
    ////////////////////////////////////////////////////////////
    function d_add($user=array()) {
        $this->newpage('user:add');
        
        $this->genform($this->fields(),plaintext($user),array(
            'img'=>'identity',
        ));
    }
    function p_add($user=array()) {
        if (!$this->readform($user,$this->fields())) return $this->d_add($user);
        
        if (!$this->cpu('user','add',$user)) return $this->d_add($user);
        
        $this->d_main();
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit user
    ////////////////////////////////////////////////////////////
    function d_edit($userid=false,$user=array()) {
        if (!$user = $this->v_userid($userid,$user)) return $this->d_main();
        $this->newpage('user:edit','user='.$userid);
        
        $this->addline('<img src="'.$user['gravatar'].'&amp;s=70" style="float:right; width:70px; position:relative; top:-15px;" />');
        $this->genform($this->fields(),$user,array(
            'img'=>'identity',
        ));
    }
    function p_edit($userid=false,$user=array()) {
        if (!$olduser = $this->v_userid($userid)) return $this->d_main();
        
        if (!$this->readform($user,$this->fields())) return $this->d_edit($userid,$user);
        
        if (!$this->cpu('user','edit',$userid,$user)) return $this->d_edit($userid,$user);
        
        $this->d_main();
    }
}
?>