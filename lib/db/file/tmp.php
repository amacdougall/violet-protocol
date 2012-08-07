<?php
import('.../lib/db/file/lib.php');

class db_tmp extends db_flib {
    var $filepath = 'tmp/files/';
    
    
    
    // Load tmp file as file object
    function get($uri,$check=false) {
        $uri = str_replace('tmp:///','',$uri);
        return load_file($this->path.$this->filepath.$uri,$check);
    }
    
    
    
    function save($from,$id=false) {
        if (is_array($from)) $from = $from['uri'];
        
        // Generate new ID
        if (!$id) {
            while ( (!$id) || (canfind_file($this->path.$this->filepath.$id)) ) $id = rand(10000,99999);
        }
        
        // Save new file
        $file = load_file($this->path.$this->filepath.filesafe($id));
        if (substr($from,0,7)=='tmp:///') {
            $from = load_file($from);
            $file->write($from->read('string'));
        } else $file->copy($from);
        
        return $id;
    }
    
    
    
    function delete($id) {
        $file = load_file($this->path.$this->filepath.filesafe($id));
        $file->delete($file);
        return $id;
    }
}
?>