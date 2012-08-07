<?php
import('.../lib/db/file/idbase.php');

class db_user extends db_flib_idbase {
    // User file layout
    var $layout = array(
        'session'=>'string',
        'ip'=>'string',
        'group'=>'int',
        'name'=>'string',
        'password'=>'string',
        'email'=>'string',
    );
    
    // db_flib_idbase info
    var $perfile = 0;
    var $filename = 'private/users.php';
    
    
}
?>