<?php
import('.../lib/admin/config.php');
class admin_usergroup extends admin_config {
    // Set variables for usergroup subsection
    var $navigation = array(
        'usergroup:main'=>false,
        'usergroup:add'=>'usergroup:main',
        'usergroup:edit'=>'usergroup:edit|usergroup:main',
        'usergroup:delete'=>'usergroup:edit|usergroup:main',
    );
    var $lang = 'usergroup';
    var $subsection = 'usergroup';
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch) {
        switch ($switch) {
            case 'add': case 'edit':
                $fields = array(
                    'name'=>'text',
                );
                
                if ($switch=='add') {
                    // Add: just copies other permissions
                    $fields['copy'] = new item_list('usergroup');
                } elseif ($switch=='edit') {
                    // Edit: allows editing each permission
                    $fields[] = array('type'=>'html','html'=>'<h2>'.get_lang('usergroup','permissions').'</h2>');
                    foreach ($this->cpu('usergroup','get_permissions') as $perm) {
                        $fields[] = array('type'=>'html','html'=>'<div style="float:left; width:300px;">');
                        $fields[$perm] = array('type'=>'checkbox','title'=>(get_lang('permission',$perm,0,0,1)?get_lang('permission',$perm):get_lang('title',$perm)));
                        $fields[] = array('type'=>'html','html'=>'</div>');
                    }
                    $fields[] = array('type'=>'html','html'=>'<p style="clear:left;"></p>');
                }
                return $fields;
                break;
            case 'delete':
                return array(
                    'move'=>new item_list('usergroup'),
                );
                break;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Validate and retreive requested usergroup
    ////////////////////////////////////////////////////////////
    function v_usergroupid(&$usergroupid,$data=array()) {
        if (!setvar($usergroupid,'g','usergroup')) {
            $this->warning('usergroupnoid');
            return false;
        }
        
        if (!$usergroup = $this->v_batch('usergroup',$usergroupid)) {
            $this->error('usergroupbadid');
            return false;
        }
        
        if ($data) return $this->cpu('usergroup','format',$data,$usergroup);
        return $usergroup;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Usergroup main page
    ////////////////////////////////////////////////////////////
    function d_main() {
        $this->newpage('usergroup:main');
        
        if (!$usergroups = plaintext($this->cpu('usergroup','get'))) return false;
        
        $list = new admin_editlist;
        foreach($usergroups as $group) {
            $list->add(array(
                'id'=>$group['id'],
                'delete'=>'?p=config:usergroup:delete&amp;usergroup='.$group['id'],
                'delete_title'=>get_lang('title','configusergroup:delete'),
                'url'=>'?p=config:usergroup:edit&amp;usergroup='.$group['id'],
                'title'=>$group['name'],
            ));
        }
        $this->addline($list->render());
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add usergroup
    ////////////////////////////////////////////////////////////
    function d_add($usergroup=array()) {
        $this->newpage('usergroup:add');
        
        $this->genform($this->fields('add'),plaintext($usergroup),array(
            'img'=>'add_group',
        ));
    }
    function p_add($usergroup=array()) {
        if (!$this->readform($usergroup,$this->fields('add'))) return $this->d_add($usergroup);
        
        // Copy another group's permissions
        if (!$copygroup = $this->cpu('usergroup','get',$usergroup['copy'])) return $this->d_add($usergroup);
        $usergroup = array_merge($copygroup,$usergroup);
        
        if (!$usergroup = $this->cpu('usergroup','add',$usergroup)) return $this->d_add($usergroup);
        
        $this->d_edit($usergroup['id']);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit usergroup
    ////////////////////////////////////////////////////////////
    function d_edit($usergroupid=false,$usergroup=array()) {
        if (!$usergroup = $this->v_usergroupid($usergroupid,$usergroup)) return $this->d_main();
        $this->newpage('usergroup:edit','usergroup='.$usergroupid);
        
        foreach ($usergroup['permissions'] as $id => $perm) $usergroup[$id] = $perm;
        
        $this->genform($this->fields('edit'),$usergroup,array(
            'img'=>'login_manager',
        ));
    }
    function p_edit($usergroupid=false,$usergroup=array()) {
        if (!$oldusergroup = $this->v_usergroupid($usergroupid)) return $this->d_main();
        
        if (!$this->readform($usergroup,$this->fields('edit'))) return $this->d_edit($usergroupid,$usergroup);
        
        $usergroup['permissions'] = array();
        foreach ($usergroup as $id => $value) if (!is_array($value)) $usergroup['permissions'][$id] = $value;
        
        if (!$this->cpu('usergroup','edit',$usergroupid,$usergroup)) return $this->d_edit($usergroupid,$usergroup);
        
        $this->d_main();
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete usergroup
    ////////////////////////////////////////////////////////////
    function d_delete($usergroupid=false) {
        if (!$usergroup = $this->v_usergroupid($usergroupid)) return $this->d_main();
        $this->newpage('usergroup:delete','usergroup='.$usergroupid);
        
        $this->genform($this->fields('delete'),plaintext($usergroup),array(
            'img'=>'editdelete',
        ));
    }
    function p_delete($usergroupid=false,$usergroup=array()) {
        if (!$oldusergroup = $this->v_usergroupid($usergroupid)) return $this->d_main();
        
        if (!$this->readform($usergroup,$this->fields('delete'))) return $this->d_delete($usergroupid);
        
        // Move users to another group
        if (!$movegroup = $this->cpu('usergroup','get',$usergroup['move'])) return $this->d_delete($usergroupid);
        if ($movegroup['id'] == $oldusergroup['id']) {
            $this->error('same_move');
            return $this->d_delete($usergroupid);
        }
        foreach ($this->cpu('user','search',$oldusergroup['id'],'group') as $user) {
            $user['group'] = $movegroup['id'];
            $this->cpu('user','edit',$user['id'],$user);
        }
        
        if (!$this->cpu('usergroup','delete',$usergroupid)) return $this->d_delete($usergroupid);
        
        $this->d_main();
    }
}
?>