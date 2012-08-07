<?php
import('.../lib/db/file/namebase.php');

class db_file extends db_flib_namebase {
    // File list file layout
    var $layout = array(
        'name'=>'string',
        'order'=>'array',
        'templates'=>'array',
    );
    
    // db_flib_namebase info
    var $indexname = 'templates/files.db';
    
    
    
    function loaddata($line) {
        $file = parent::loaddata($line);
        
        // Foramat template data
        $templates = array();
        foreach ($file['templates'] as $key => $data) {
            $data = $this->destorify(explode(',',$data));
            $templates[($key+1)] = array(
                'id'=>$key+1,
                'name'=>$data[0],
                'trigger'=>(isset($data[1])?$data[1]:false),
            );
        }
        $file['templates'] = $templates;
        return $file;
    }
    function savedata($file) {
        // Support nested associative array
        if (isset($file['templates'])) {
            $file['templates'] = $this->storify($file['templates']);
            foreach ($file['templates'] as $key => $data) $file['templates'][$key] = $data['name'].','.$data['trigger'];
        }
        
        return parent::savedata($file);
    }
}
?>