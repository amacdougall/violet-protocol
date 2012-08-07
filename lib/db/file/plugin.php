<?php
import('.../lib/db/file/namebase.php');

class db_plugin extends db_flib_namebase {
    // Plugin list file layout
    var $layout = array(
        'name'=>'string',
        'title'=>'string',
        'types'=>'array',
    );
    
    // db_flib_namebase info
    var $indexname = 'plugins/plugins.db';
    
    
    
    function get($id=false,$extra=false) {
        // Support advanced input
        if ($extra) {
            switch ($id) {
                case 'event':
                    // Get plugins by event
                    $eventdb = load_db('event');
                    $event = $eventdb->get($extra);
                    $result = array();
                    
                    // For each event
                    if (parse_dbresult($event)) {
                        foreach ($event['plugins'] as $pid) $result[] = $this->get($pid,'nosettings');
                    }
                    
                    $result['result'] = (empty($result)?'empty':'good');
                    return $result;
                    break;
                
                case 'type':
                    // Get plugin by type
                    return $this->search($extra,'types');
                    break;
            }
        }
        
        // Load plugin data
        $result = parent::get($id,$extra);
        
        // Load plugin's settings if only loading one plugin
        if ( (isset($result['name'])) && ($extra != 'nosettings') ) {
            if ($file = $this->getfile('plugins/'.foldersafe($result['name']).'/settings.dta',true)) $result['settings'] = $this->unstore_array($file->read('string'));
        }
        
        return $result;
    }
    
    
    
    function save($plugin,$id=false) {
        // Save plugin data
        $result = parent::save($plugin,$id);
        
        if (!$id) {
            if (!$file = $this->getfile('plugins/'.foldersafe(($id?$id:$plugin['name'])).'/settings.dta',true)) {
                // Default plugin settings if not already present
                $file = $this->getfile('plugins/'.foldersafe(($id?$id:$plugin['name'])).'/settings.dta');
                $file->make_directory();
                $file->write($this->store_array($plugin['settings']));
            }
        } elseif ( ($result) && (isset($plugin['settings'])) ) {
            // Edit plugin's settings
            $file = $this->getfile('plugins/'.foldersafe(($id?$id:$plugin['name'])).'/settings.dta');
            $file->write($this->store_array($plugin['settings']));
        }
        
        return $result;
    }
    
    
    
    function delete($id,$full=false) {
        // Delete from index
        parent::delete($id);
        
        if ($full) {
            // Delete settings
            import('.../lib/filesystem.php');
            delete_directory($this->path.'plugins/'.foldersafe($id).'/');
        }
    }
}















/*

////////////////////////////////////////////////////////////
// Convert plugin storage string into a usable array
////////////////////////////////////////////////////////////
function plugin_data($line) {
    if (strpos($line,'|')) {
        $plugin = array('comic'=>false,'news'=>false,'action'=>false,'event'=>false,'template'=>false);
        $data = unstoreline($line);
        for($x=0;$x<5;$x++) {
            switch(substr($data[0],$x,1)) {
                case 'C': $plugin['comic'] = true; break;
                case 'N': $plugin['news'] = true; break;
                case 'A': $plugin['action'] = true; break;
                case 'E': $plugin['event'] = true; break;
                case 'T': $plugin['template'] = true; break;
            }
        }
        $plugin['id'] = substr($data[0],5);
        $plugin['name'] = $data[1];
        $plugin['desc'] = $data[2];
        return $plugin;
    }
    return false;
}



////////////////////////////////////////////////////////////
// Return plugin
////////////////////////////////////////////////////////////
function db_return_plugin($pid='#all') {
    $file = file_array('.../storage/plugins/plugins.db');
    if ($pid=='#all') {
        $plugins = array('result'=>'good');
        if (count($file)>=1) {
            foreach ($file as $line) {
                $plugin = plugin_data($line);
                if ($plugin) {
                    $plugins[$plugin['id']] = $plugin;
                }
            }
        }
        return $plugins;
    } else {
        if (count($file)>=1) {
            foreach ($file as $line) {
                $checkp = plugin_data($line);
                if ($checkp['id'] == $pid) {
                    $plugin = $checkp;
                    $plugin['result'] = 'good';
                }
            }
        }
        if (!isset($plugin)) {
            return array('result'=>'error');
        }
        $file = file_string('.../storage/plugins/' . $pid . '.dta');
        $plugin['settings'] = destorify(unserialize($file));
        return $plugin;
    }
}



////////////////////////////////////////////////////////////
// Search plugins
////////////////////////////////////////////////////////////
function db_search_plugins($attr,$value,$returnfirst=false,$casesensitive=false) {
    $file = file_array('.../storage/plugins/plugins.db');
    $plugins = ($returnfirst?false:array());
    
    foreach ($file as $line) {
        $plugin = plugin_data($line);
        if ( (isset($plugin[$attr])) && ( ($plugin[$attr] == $value) || ((!$casesensitive) && (strtolower($plugin[$attr]) == strtolower($value))) ) ) {
            $plugins[$plugin['id']] = $plugin;
            $plugins['result'] = 'good';
        }
    }
    
    $plugins['result'] = (isset($plugins['result'])?$plugins['result']:'empty');
    return $plugins;
}



////////////////////////////////////////////////////////////
// Return plugins attached to event
////////////////////////////////////////////////////////////
function db_return_eventplugins($event='#all') {
    $file = file_array('.../storage/plugins/events.db');
    if ($event=='#all') {
        $result = array('result'=>'good');
        foreach ($file as $line) {
            if (strpos($line,'|')) {
                $data = explode('|',rtrim($line));
                $result['on_' . $data[0]] = explode(',',$data[1]);
                $result['after_' . $data[0]] = explode(',',$data[2]);
            }
        }
        return $result;
    } else {
        $type = substr($event,0,strpos($event,'_'));
        $event = substr($event,strpos($event,'_')+1);
        foreach ($file as $line) {
            $data = explode('|',rtrim($line));
            if ($data[0] == $event) {
                $segment = $data[($type=='on'?1:2)];
                $return = ($segment?explode(',',$segment):array());
                $return['result'] = (empty($return)?'empty':'good');
                return $return;
            }
        }
        return array('result'=>'error');
    }
}



////////////////////////////////////////////////////////////
// Save plugin events
////////////////////////////////////////////////////////////
function db_save_events($events) {
    $file = '';
    foreach ($events as $event => $calls) {
        $file .= storeline(array(
            $event,
            storeline($calls['on'],','),
            storeline($calls['after'],','),
        )) . "\n";
    }
    write_file('.../storage/plugins/events.db','w',$file);
}



////////////////////////////////////////////////////////////
// Return plugin attached to action
////////////////////////////////////////////////////////////
function db_return_actionplugin($action='#all') {
    $file = file_array('.../storage/plugins/actions.db');
    if ($action=='#all') {
        $result = array('result'=>'good');
        foreach ($file as $line) {
            if (strpos($line,'|')) {
                $data = explode('|',$line);
                $result[rtrim($data[1])][] = $data[0];
            }
        }
        return $result;
    } else {
        foreach ($file as $line) {
            $data = explode('|',$line);
            if ($data[0] == $action) {
                return db_return_plugin(rtrim($data[1]));
            }
        }
        return array('result'=>'error');
    }
}



////////////////////////////////////////////////////////////
// Return plugin by type
////////////////////////////////////////////////////////////
function db_return_plugintype($type) {
    $file = file_array('.../storage/plugins/plugins.db');
    $plugins = array();
    if (count($file)>=1) {
        foreach ($file as $line) {
            $checkp = plugin_data($line);
            if ($checkp[$type]) {
                $file = file_string('.../storage/plugins/' . $checkp['id'] . '.dta');
                $checkp['settings'] = destorify(unserialize($file));
                $plugins[] = $checkp;
            }
        }
    }
    if (empty($plugins)) return array('result'=>'empty');
    $plugins['result']='good';
    return $plugins;
}



////////////////////////////////////////////////////////////
// Attach action to plugin
////////////////////////////////////////////////////////////
function db_insert_pluginaction($action,$plugin) {
    $line = storeline(array(
        $action,
        $plugin,
    )) . "\n";
    write_file('.../storage/plugins/actions.db','a',$line);
}



////////////////////////////////////////////////////////////
// Save plugin settings
////////////////////////////////////////////////////////////
function db_save_plugin($pid,$settings) {
    write_file('.../storage/plugins/' . $pid . '.dta','w',serialize(storify($settings)));
}



////////////////////////////////////////////////////////////
// Add plugin
////////////////////////////////////////////////////////////
function db_insert_plugin($plugin) {
    $newline = storeline(array(
        (isset($plugin['E'])?'E':'0').(isset($plugin['A'])?'A':'0').(isset($plugin['U'])?'U':'0').(isset($plugin['T'])?'T':'0').$plugin['id'],
        $plugin['author'],
        $plugin['title'],
        $plugin['desc'],
    )) . "\n";
    write_file('.../storage/plugins/plugins.db','a',$newline);
}



////////////////////////////////////////////////////////////
// Delete plugin
////////////////////////////////////////////////////////////
function db_delete_plugin($plugin) {
    delete_file('.../storage/plugins/'.$plugin.'.dta');
    
    $file = file_array('.../storage/plugins/plugins.db');
    foreach ($file as $k => $line) {
        $checkp = plugin_data($line);
        if ($checkp['id'] == $plugin) {
            $line[$k]='';
        }
    }
    write_file('.../storage/plugins/plugins.db','w',$file);
}
*/
?>