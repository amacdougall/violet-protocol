<?php
import('.../lib/db/file/namebase.php');

class db_setting extends db_flib_namebase {
    // Setting file layout
    var $layout = array(
        'name'=>'string',
        'type'=>'string',
        'value'=>'string',
    );
    
    // db_flib_namebase info
    var $indexname = 'private/settings.php';
    
    
    
    function loaddata($line,$id=false) {
        $setting = parent::loaddata($line,$id);
        
        switch ($setting['type']) {
            case 'bool': $setting['value'] = ($setting['value']?true:false); break;
            case 'num': $setting['value'] = $setting['value']*1; break;
            case 'array': case 'harray': $setting['value'] = (is_array($setting['value'])?$setting['value']:($setting['value']?array($setting['value']):array())); break;
        }
        
        return $setting;
    }
    function savedata($setting) {
        switch ($setting['type']) {
            case 'bool': $setting['value'] = ($setting['value']?1:0); break;
            case 'num': $setting['value'] = $setting['value']*1; break;
        }
        
        return parent::savedata($setting);
    }
    
    
    
    function saveall($settings) {
        $file = $this->getfile($this->indexname);
        
        // Create file
        $s = $this->get();
        parse_dbresult($s);
        
        foreach ($s as $name => $setting) {
            if (isset($settings[$name])) $s[$name]['value'] = $settings[$name];
            $lines[$name] = $this->savedata($s[$name]);
        }
        
        $file->write($lines);
    }
}
?>