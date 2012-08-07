<?php
import('.../lib/db/file/lib.php');

////////////////////////////////////////////////////////////
// Extendable class for items that use name IDs
// Acts as a base for tags, plugins, etc.
////////////////////////////////////////////////////////////
class db_flib_namebase extends db_flib {
    var $basetype = 'name';
    var $itemcache = array();
    
    // Default data, extending classes should change these
    var $indexname = false;
    
    
    
    function get($id=false,$extra=false) {
        // Allow advanced-style input
        switch ($id) {
            case 'all': $id = false; break;
            case 'single': $id = $extra; break;
        }
        
        // Load file
        $file = $this->getfile($this->indexname);
        $arr = $file->read('array');
        
        $result = array();
        
        if ($id) {
            // Find ID
            $id = $this->storify($id);
            while ( (current($arr)) && (substr(strtolower(current($arr)),0,strlen($id)+1) != strtolower($id).'|') ) next($arr);
            if (strpos(current($arr),'|')) $result = $this->loaddata(current($arr));
        } else {
            // Every item
            foreach ($arr as $line) {
                $r = $this->loaddata($line);
                $result[$r['name']] = $r;
            }
        }
        
        if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    function save($item,$id=false) {
        if (!is_array($item)) return false;
        
        if ($id) {
            // Allow for partial updates
            $original = $this->get($id);
            if (parse_dbresult($original)) $item = array_merge($original,$item);
        }
        
        $line = $this->savedata($item);
        
        // Load file
        $file = $this->getfile($this->indexname);
        
        if ($id) {
            $arr = $file->read('array');
            
            // Find item to overwrite
            while (substr(strtolower(current($arr)),0,strlen($id)+1) != strtolower($id).'|') next($arr);
            if (strpos(current($arr),'|')) {
                $arr[key($arr)] = $line;
            } else return false;
            
            $file->write($arr);
            
        // New item
        } else $file->append($line);
        
        return $item['name'];
    }
    
    
    
    function search($find,$attribute=false) {
        // Load file
        $file = $this->getfile($this->indexname);
        $arr = $file->read('array');
        
        $result = array();
        foreach ($arr as $line) {
            // Quick search
            if (strpos(' '.strtolower($line),strtolower($find))) {
                // In depth search & format
                $item = $this->loaddata($line);
                if (!$attribute) {
                    $result[] = $item;   
                } elseif ( (is_array($item[$attribute])) && (in_array($find,$item[$attribute])) ) {
                    $result[] = $item;
                } elseif (strtolower($item[$attribute])==strtolower($find)) {
                    $result[] = $item;
                }
            }
        }
        
        // Format response
        if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    function delete($id) {
        // Load file
        $file = $this->getfile($this->indexname);
        $arr = $file->read('array');
        
        // Find item to delete
        $a = false;
        $id = $this->storify($id);
        while ( ( (!$a) || (next($arr)) ) && (substr(strtolower(current($arr)),0,strlen($id)+1) != strtolower($id).'|') ) $a = true;
        if (strpos(current($arr),'|')) $arr[key($arr)] = '';
        
        $f = str_replace("\n\n","\n",implode($arr));
        if (substr($f,0,1)=="\n") $f = substr($f,1);
        $file->write($f);
    }
    
    
    
    function loaddata($line) {
        return $this->linetodata($line,$this->layout);
    }
    function savedata($item) {
        return $this->datatoline($item,$this->layout);
    }
}
?>