<?php
import('.../lib/db/file/lib.php');

class db_quickcache extends db_flib {
    var $filepath = 'cache/';
    
    function get($id,$subid=false,$searchid=false) {
        if (!$file = $this->getfile($this->filepath.filesafe($id.'.'.$subid.'.'.$searchid.'.cache'),true)) return false;
        $array = $file->read('array');
        $vars = $this->unstore_array(array_shift($array));
        return array(
            'content'=>implode($array),
            'vars'=>$vars,
        );
    }
    
    function get_globals() {
        if (!$header = $this->getfile($this->filepath.'header.cache',true)) return false;
        if (!$footer = $this->getfile($this->filepath.'footer.cache',true)) return false;
        
        return array(
            'header'=>$header->read('string'),
            'footer'=>$footer->read('string'),
        );
    }
    
}