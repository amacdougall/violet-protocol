<?php
// 
// Tar class
// Unpacks and creates tar files (optional .gz compression)
// 
// (c) Steve H 2008 :: GPL v3
// 
class class_tar {
    // Initial variables
    var $file = false;
    var $filename = false;
    var $pointer = 0;
    var $marks = array();
    var $curdata = false;
    var $filelist = array();
    var $traversed = false;
    
    
    function class_tar($file=false,$gz=false) {
        if ($file) $this->load($file,$gz);
    }
    
    // Load tar file
    function load($file,$gz=false) {
        $this->close();
        
        if ( ($gz) && (!function_exists('gzopen')) ) {
            trigger_error('gz compression not supported by this PHP installation',E_USER_ERROR);
            return false;
        }
        if (!$this->file = ($gz?gzopen($file,'rb'):fopen($file,'rb'))) {
            trigger_error('tar file failed to load',E_USER_ERROR);
            return false;
        }
        $this->filename = $file;
        $this->reset();
        return true;
    }
    
    // Close tar file
    function close() {
        if ($this->file) {
            $this->reset();
            fclose($this->file);
        }
        $this->file = false;
        $this->filename = false;
        $this->marks = array();
        $this->filelist = array();
        $this->traversed = false;
        return true;
    }
    
    
    
    // Reset internal file pointer
    function reset() {
        if (!$this->file) return $this->nofile();
        rewind($this->file);
        $this->pointer = 0;
        $this->curdata = false;
        return $this->loadcur();
    }
    
    
    
    // Set pointer to previous file
    function prev() {
        if (!$this->file) return $this->nofile();
        
        if ($this->pointer == 0) return false;
        $this->pointer--;
        
        fseek($this->file,$this->marks[$this->pointer]);
        return $this->loadcur();
    }
    // Set pointer to next file
    function next() {
        if (!$this->file) return $this->nofile();
        
        $this->pointer++;
        
        return $this->loadcur();
    }
    // Goto specified file
    function seek($i) {
        if ($this->pointer == $i) return true;
        if ($this->pointer > $i) {
            fseek($this->file,$this->marks[$i]);
            return $this->loadcur();
        }
        while ( ($r = $this->next()) && ($this->pointer < $i) );
        return $r;
    }
    // Load selected file
    function loadcur() {
        $pointer = ftell($this->file);
        
        $headers = fread($this->file,512);
        if ($headers == str_repeat(chr(0),512)) {
            $this->traversed = true;
            return false;
        }
        
        $this->curdata = array(
            'name'=>str_replace(chr(0),'',substr($headers,0,100)),
            'mode'=>octdec(substr($headers,100,8)),
            'uid'=>octdec(substr($headers,108,8)),
            'gid'=>octdec(substr($headers,116,8)),
            'size'=>octdec(substr($headers,124,12)),
            'time'=>octdec(substr($headers,136,12)),
            'checksum'=>octdec(substr($headers,148,6)),
            'type'=>octdec(substr($headers,156,1)),
        );
        
        if (substr($headers,257,5) == 'ustar') {
            $this->curdata['name'] = str_replace(chr(0),'',substr($headers,345,155)).$this->curdata['name'];
        }
        
        if ($this->checksum($headers) != $this->curdata['checksum']) {
            trigger_error('Tar checksum failed',E_USER_ERROR);
            return false;
        }
        
        $this->marks[$this->pointer] = $pointer;
        $this->filelist[$this->pointer] = $this->curdata['name'];
        
        fseek($this->file,($pointer+512+(ceil($this->curdata['size']/512)*512)));
        
        return true;
    }
    
    
    
    function checksum($str) {
        $sum = 256;
        for ($n=0;$n<512;$n++) if (($n<148)||($n>155)) $sum += ord($str[$n]);
        return $sum;
    }
    
    
    
    // Return file data
    function data($data=false) {
        if (!$this->file) return $this->nofile();
        return $this->curdata;
    }
    // Return file contents
    function curfile() {
        if (!$this->file) return $this->nofile();
        if ($this->curdata['size']) {
            fseek($this->file,($this->marks[$this->pointer]+512));
            $data = fread($this->file,$this->curdata['size']);
            fseek($this->file,($this->marks[$this->pointer]+512+(ceil($this->curdata['size']/512)*512)));
            return $data;
        } else return true;
    }
    
    
    
    // Return filelist of entire tar file
    function filelist() {
        if ($this->traversed) return $this->filelist;
        if (!$this->reset()) return array(); // Empty file?
        while ($this->next());
        return $this->filelist;
    }
    
    
    
    // Trigger class error
    function nofile() {
        trigger_error('No file loaded in class_tar',E_USER_WARNING);
        return false;
    }
    
    
    
    
    
    
    
    function create($file,$gz=false) {
        $this->close();
        $this->filename = $file;
        
        if ( ($gz) && (!function_exists('gzopen')) ) {
            trigger_error('gz compression not supported by this PHP installation',E_USER_ERROR);
            return false;
        }
        if (!$this->file = ($gz?gzopen($file,'wb'):fopen($file,'wb'))) {
            trigger_error('Archive cannot be written',E_USER_ERROR);
            return false;
        }
    }
    
    function add($filename,$path=false) {
        if (!$this->file) return $this->nofile();
        
        if (!$path) $path = $filename;
        if (!file_exists($path)) {
            trigger_error('Path not found',E_USER_ERROR);
            return false;
        }
        
        // Check file is readable
        if (!is_dir($path)) {
            if ($file = fopen($path,'r')) {
                fclose($file);
            } else {
                trigger_error('File failed to load',E_USER_ERROR);
                return false;
            }
        }
        
        // Check filename length
        if (strlen($filename) > 255) {
            trigger_error('Filename too long',E_USER_ERROR);
            return false;
        }
        
        // Pull info
        $info = stat($path);
        $this->filelist[$filename] = array(
            'path'=>$path,
            'name'=>$filename,
            'mode'=>$info['mode'],
            'uid'=>$info['uid'],
            'gid'=>$info['gid'],
            'size'=>(is_dir($path)?0:$info['size']),
            'time'=>$info['mtime'],
            'type'=>(is_dir($path)?5:0),
        );
    }
    
    function addDir($dirname,$path=false,$ignore=true) {
        if (!$this->file) return $this->nofile();
        
        if (!$path) $path = $dirname;
        if (!file_exists($path)) {
            trigger_error('Path not found',E_USER_ERROR);
            return false;
        }
        
        if (substr($dirname,-1)!='/') $dirname .= '/';
        if (substr($path,-1)!='/') $path .= '/';
        
        $this->add($dirname,$path);
        $handle = opendir($path);
        while ($f = readdir($handle)) {
            if ( ($f!='.') && ($f!='..') && ( (!$ignore) || (substr($f,-1)!='~') ) ) {
                if (is_dir($path.$f)) {
                    $this->add($dirname.$f.'/',$path.$f.'/');
                    $this->addDir(($dirname.$f.'/'),($path.$f.'/'));
                } else $this->add($dirname.$f,$path.$f);
            }
        }
    }
    
    function save() {
        foreach (array(5,0) as $type) {
            foreach ($this->filelist as $file) if ($file['type'] == $type) {
                if (strlen($file['name']) > 100) {
                    $file['prefix'] = substr($file['name'],0,(strlen($file['name'])-100));
                    $file['name'] = substr($file['name'],-100);
                }
                
                // Basic info
                $headers = str_pad($file['name'],100,chr(0));
                $headers .= str_pad(decoct($file['mode']),7,"0",STR_PAD_LEFT) . chr(0);
                $headers .= str_pad(decoct($file['uid']),7,"0",STR_PAD_LEFT) . chr(0);
                $headers .= str_pad(decoct($file['gid']),7,"0",STR_PAD_LEFT) . chr(0);
                $headers .= str_pad(decoct($file['size']),11,"0",STR_PAD_LEFT) . chr(0);
                $headers .= str_pad(decoct($file['time']),11,"0",STR_PAD_LEFT) . chr(0);
                $headers .= "        ".$file['type'].str_repeat(chr(0),100);
                
                // Longname
                $headers .= "ustar".chr(32).chr(32).chr(0);
                $headers .= str_pad('',32,chr(0));
                $headers .= str_pad('',32,chr(0));
                $headers .= str_repeat(chr(0),8);
                $headers .= str_repeat(chr(0),8);
                $headers .= str_pad((isset($file['prefix'])?$file['prefix']:''),155,chr(0));
                $headers .= str_repeat(chr(0),12);
                
                // Checksum
                $checksum = str_pad(decoct($this->checksum($headers)),6,"0",STR_PAD_LEFT).chr(0).chr(32);
                $headers = substr($headers,0,148).$checksum.substr($headers,156);
                
                fwrite($this->file,$headers);
                unset($headers);
                
                if ($file['size'] > 0) {
                    $pad = str_repeat(chr(0),((ceil($file['size']/512)*512)-$file['size']));
                    
                    $r = fopen($file['path'],'rb');
                    while ($write = fread($r,512)) {
                        fwrite($this->file,$write);
                    }
                    fclose($r);
                    fwrite($this->file,$pad);
                    
                }
            }
        }
        fwrite($this->file,str_repeat(chr(0),512));
        fclose($this->file);
        $this->filelist = array();
    }
    
    
}

?>