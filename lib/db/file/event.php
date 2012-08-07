<?php
import('.../lib/db/file/namebase.php');

class db_event extends db_flib_namebase {
    // Event list file layout
    var $layout = array(
        'name'=>'string',
        'plugins'=>'array',
    );
    
    // db_flib_namebase info
    var $indexname = 'plugins/events.db';
    
}
?>