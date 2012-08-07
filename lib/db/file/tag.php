<?php
import('.../lib/db/file/namebase.php');

class db_tag extends db_flib_namebase {
    // Tag collection file layout
    var $layout = array(
        'name'=>'string',
        'tags'=>'array',
    );
    
    // db_flib_namebase info
    var $indexname = 'comics/collections.dta';
    
    
    
    function loaddata($line) {
        $collection = parent::loaddata($line);
        
        $collection['id'] = strtolower($collection['name']);
        return $collection;
    }
    
    
    
    function savedata($collection) {
        // Sanitize tags
        $collection['tags'] = simpletext($collection['tags']);
        
        return parent::savedata($collection);
    }
}
?>