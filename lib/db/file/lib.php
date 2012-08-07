<?php
$GLOBALS['dbfileclass'] = array();


//
// Core file database class
//
class db_flib {
    var $type = 'file';
    var $version = 4;
    var $path = '.../storage/';
    
    var $cache = array();
    
    
    
    
    function clearcache() {
        $this->cache = array();
    }
    
    
    
    
    
    function getfile($uri,$check=false,$poly=false) {
        // Support temporary files
        if (substr($uri,0,7)=='tmp:///') {
            $tmpdb = load_db('tmp');
            return $tmpdb->get($uri,$check);
        }
        
        if ( (substr($uri,0,4)!='.../') && (substr($uri,0,1)!='/')) $uri = $this->path . $uri;
        
        // Check file exists
        if ( ($check) && (!canfind_file($uri)) ) return false;
        
        // Check for memcache
        if ( ($poly) || (!isset($GLOBALS['dbfileclass'][makepath($uri)])) ) {
            $file = new db_fileclass($uri);
            
            // Just return class if wanted
            if ($poly) return $file;
            
            // Create & Return memlink to global class
            $GLOBALS['dbfileclass'][makepath($uri)] = $file;
        }
        $r = &$GLOBALS['dbfileclass'][makepath($uri)];
        
        return $r;
    }
    
    
    
    function linetodata($line,$layout) {
        $line = explode('|',rtrim($line));
        
        $result = array();
        $index=0;
        foreach ($layout as $name => $type) {
            // Populate associative array
            if (isset($line[$index])) {
                $result[$name] = $line[$index];
            } else $result[$name] = false;
            
            // CSV to array
            if (strpos($result[$name],',')!==false) {
                $result[$name] = explode(',',$result[$name]);
            }
            
            // Sanitize
            switch($type) {
                case 'bool': $result[$name] = ($result[$name]?true:false); break;
                case 'int': if ($result[$name]!=='') $result[$name]*=1; break;
                case 'storedarray':
                    $result[$name] = $this->unstore_array($result[$name]);
                    // Also carry on to ensure array and destorify
                case 'array':
                    if (!is_array($result[$name])) {
                        $result[$name] = ($result[$name]?array($result[$name]):array());
                    }
                    // Also carry on to default to destorify
                default:
                    $result[$name] = $this->destorify($result[$name]);
                    break;
            }
            
            $index++;
        }
        return $result;
    }
    
    
    
    function datatoline($line,$layout) {
        $result = '';
        foreach ($layout as $name => $type) {
            if (isset($line[$name])) {
                $value = $line[$name];
            } else $value = false;
            
            // Bool to int
            if ($type=='bool') $value = ($value?1:0);
            
            // Stored Text
            $value = $this->storify($value);
            
            // Array to CSV
            if (is_array($value)) {
                if ($type=='storedarray') {
                    $value = $this->store_array($value);
                } else $value = implode(',',$value);
            }
            
            // Populate line
            $result .= $value.'|';
        }
        return $result."\n";
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Convert strings into storable / readable strings
    ////////////////////////////////////////////////////////////
    function storify($inp,$allow=false) {
        if (is_array($inp)) {
            foreach ($inp as $key => $value) $inp[$key] = $this->storify($value,$allow);
            return $inp;
        }
        $change = array('&'=>'&a',"\n"=>'&b',"\r"=>'','|'=>'&p',','=>'&c','.'=>'&d');
        if ($allow) unset($change[$allow]);
        foreach ($change as $from => $to) $inp = str_replace($from,$to,$inp);
        return $inp;
    }
    function destorify($inp) {
        if (is_array($inp)) {
            foreach ($inp as $key => $value) $inp[$key] = $this->destorify($value);
            return $inp;
        }
        $change = array('&d'=>'.','&c'=>',','&p'=>'|','&b'=>"\n",'&a'=>'&', '&n'=>"\n");
        foreach ($change as $from => $to) $inp = str_replace($from,$to,$inp);
        return $inp;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Convert array into/out of storage
    ////////////////////////////////////////////////////////////
    function store_array($arr) {return serialize($this->storify($arr));}
    function unstore_array($line) {return (rtrim($line)?$this->destorify(unserialize(rtrim($line))):array());}
    
    
    
    ////////////////////////////////////////////////////////////
    // Get date/time data from timestamp
    ////////////////////////////////////////////////////////////
    function timestamp($timestamp) {
        return array(
            'year'=>date('Y',$timestamp),
            'month'=>date('m',$timestamp),
            'weekday'=>date('w',$timestamp),
            'day'=>date('d',$timestamp),
            'hour'=>date('H',$timestamp),
            'minute'=>date('i',$timestamp),  
        );
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Workout range names (for filenames)
    ////////////////////////////////////////////////////////////
    function rangename($id,$range=100) {
        $top=0;
        while ($top < $id) $top += $range;
        return ($top-$range+1).'-'.$top;
    }
}








////////////////////////////////////////////////////////////
// SVN Database version auto-upgrader
////////////////////////////////////////////////////////////
$v = file_string('.../storage/dbversion');
$vars = get_class_vars('db_flib');
if ($v != $vars['version']) {
    import('_dev/svnupgrade.php');
    svn_upgrade($v);
}









////////////////////////////////////////////////////////////
// Database file class
////////////////////////////////////////////////////////////
class db_fileclass extends file {
    
    function read($type=false) {
        if ($this->content===false) {
            // Load database file
            parent::read();
            
            // Private file modifications
            if (substr($this->uri,-4)=='.php') array_shift($this->content);
        }
        
        // Return content
        if ($type!==false) return parent::read($type);
    }
    
    function write($text,$chmod=false) {
        if (is_array($text)) $text = implode($text);
        
        // Private file modifications
        if (substr($this->uri,-4)=='.php') $text = '<' . '?php die(\'Die Cracker!\'); ?' . '>' . "\n" . $text;
        
        // CHMOD new files
        if ( (!$chmod) && (!canfind_file($this->uri)) ) {
            if (substr($this->uri,-4)=='.php') {
                $chmod = 600;
            } else $chmod = 666;
        }
        
        // Enforce ending line break
        if (substr($text,-1)!="\n") $text .= "\n";
        
        // Write
        return parent::write($text,$chmod);
    }
    
    function close() {
        $this->content = false;
        $this->data = false;
        unset($GLOBALS['dbfileclass'][$this->uri]);
        $this->uri = false;
    }
}
?>
