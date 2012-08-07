<?php
import('.../lib/db/file/idbase.php');

class db_comic extends db_flib_idbase {
    // Comic files layout
    var $layout = array(
        'timestamp'=>'int',
        'title'=>'string',
        'author'=>'int',
        'ext'=>'string',
        'width'=>'int',
        'height'=>'int',
        'tagline'=>'string',
        'blurb'=>'string',
        'news'=>'array',
        'tag'=>'array',
    );
    
    // db_flib_idbase info
    var $perfile = 100;
    var $filepath = 'comics/';
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete comic
    //    delete(49) will delete comic #49
    ////////////////////////////////////////////////////////////
    function delete($id) {
        // Launch delete
        parent::delete($id);
        
        // Cleanup trailing deletion gaps
        if ($id = $this->lastid()) {
            $file = $this->itemfile($id);
            $arr = $file->read('array');
            
            while (!strpos(end($arr),'|')) {
                array_pop($arr);
                
                if (empty($arr)) {
                    // This file is all out of comics
                    $file->delete();
                    $id -= $this->perfile;
                    if ($id > 0) {
                        // Go back a file and continue the clearup
                        $file = $this->itemfile($id);
                        $arr = $file->read('array');
                    } else {
                        $this->clearcache();
                        return true; // No comics left
                    }
                }
            }
        
            $this->clearcache();
            $file->write($arr);
        }
        
        return $id;
    }
    
    
    
    var $imgurl = 'http:///img/comic/';
    var $imguri = '.../img/comic/';
    ////////////////////////////////////////////////////////////
    // Save comic image
    ////////////////////////////////////////////////////////////
    function saveimg($source,$name) {
        $img = load_file($this->imguri.$name);
        $img->copy($source,666);
    }
    function deleteimg($name) {
        if (!$img = load_file($this->imguri.$name,true)) return false;
        $img->delete();
        return true;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Turn storage line into comic data
    ////////////////////////////////////////////////////////////
    function loaddata($line,$id=false) {
        // Get data
        $comic = parent::loaddata($line,$id);
        
        // Add image variables
        if (isset($comic['id'])) {
            $comic['filename'] = $comic['id'] . '.' . $comic['ext'];
            $comic['src'] = $this->imgurl . $comic['filename'];
        }
        
        if ($id) $this->itemcache[$id] = $comic;
        
        return $comic;
    }
}

?>