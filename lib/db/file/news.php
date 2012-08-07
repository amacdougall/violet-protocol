<?php
import('.../lib/db/file/idbase.php');

class db_news extends db_flib_idbase {
    // News files layout
    var $layout = array(
        'timestamp'=>'int',
        'title'=>'string',
        'author'=>'int',
        'post'=>'string',
    );
    
    // db_flib_idbase info
    var $perfile = 50;
    var $filepath = 'news/';
    
    // That's it
    // Yeah, seriously
    // The db_flib_idbase which acts as a base for comics and news holds everything news wants
}
?>