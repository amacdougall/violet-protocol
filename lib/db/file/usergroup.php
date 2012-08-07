<?php
import('.../lib/db/file/idbase.php');

class db_usergroup extends db_flib_idbase {
    // Usergroup file layout
    var $layout = array(
        'name'=>'string',
        'permissions'=>'string',
    );
    var $permissions = array(
        'panel','_superuser','_checkupdate',
        'comic','comicadd','comicedit','comiceditdata','comiceditimg','comicdelete','comicdraft:main','comicdraft:edit','comicdraft:delete',
        'comictags','comicaddcollection','comiceditcollection','comicdeletecollection',
        'news','newsadd','newsedit','newseditdata','newsdelete','newsdraft:main','newsdraft:edit','newsdraft:delete',
        'newsaddcomment','newseditcomment','newsdeletecomment',
        'plugin','pluginadd','pluginuse','pluginedit','plugindelete','pluginevent',
        'template','templateeditglobal','templateadd','templateedit','templatedelete',
        'templatepage:add','templatepage:edit','templatepage:delete',
        'config','configsetting','configupdate','configuninstall','configcache',
        'configuser:main','configuser:add','configuser:edit','configuser:delete',
        'configusergroup:main','configusergroup:add','configusergroup:edit','configusergroup:delete',
    );
    
    // db_flib_idbase info
    var $perfile = 0;
    var $filename = 'private/usergroups.php';
    
    
    
    function loaddata($line,$id=false) {
        $group = parent::loaddata($line,$id);
        
        // Get permissions in associative array
        $permissions = array();
        foreach ($this->permissions as $key => $perm) {
            $permissions[$perm] = (substr($group['permissions'],$key,1)?true:false);
        }
        $group['permissions'] = $permissions;
        
        // Rewrite memcache
        if ($id) $this->itemcache[$id] = $group;
        
        return $group;
    }
    function savedata($group) {
        // Put permissions into binary string
        $permissions = '';
        foreach ($this->permissions as $key => $perm) {
            $permissions .= ($group['permissions'][$perm]?1:0);
        }
        $group['permissions'] = $permissions;
        
        return parent::savedata($group);
    }
}
?>