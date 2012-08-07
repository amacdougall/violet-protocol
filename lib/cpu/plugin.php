<?php
import('.../lib/cpu/lib.php');

class cpu_plugin extends cpu_lib {
    var $type = 'plugin';
    
    var $db=false;
    function cpu_plugin() {$this->db = load_db('plugin');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'title'=>2,
        'types'=>'array',
    );
    
    
    
    function add($data,$file) {
        if ( (!isset($file['tmp_name'])) || (strlen($file['tmp_name']) <= 2) ) return $this->error($this->type,'nofile');
        
        if (substr($file['name'],-4)!='.php') return $this->error($this->type,'phperr');
        
        $name = simpletext(substr($file['name'],0,(strlen($file['name'])-4)));
        if ($this->get($name)) return $this->error($this->type,'exists');
        
        import('.../lib/plugin.php');
        import($file['tmp_name']);
        $file = load_file($file['tmp_name']);
        
        $class = 'pl_' . $name;
        if (!class_exists($class)) return $this->error($this->type,'noclass');
        $class = new $class;
        if (!method_exists($class,'lang')) return $this->error($this->type,'nocms');
        
        $plugin = array(
            'name'=>$name,
            'title'=>$class->lang('_title'),
            'types'=>array(),
            'settings'=>$class->settings,
        );
        
        // Check permitted versions
        if ( (!@isset($class->forversion)) || (array_search(get_config('version'),$class->forversion)===false) ) return $this->error($this->type,'versionerror');
        
        // Check for actions
        if (method_exists($class,'a_'.$plugin['name'])) $plugin['types'][] = 'action';
        
        // Check for templates
        if ( (@isset($class->template)) && ($class->template) ) {
            $plugin['types'][] = 'template';
            if (is_array($class->template)) {
                if (in_array('comic',$class->template)) $plugin['types'][] = 'comic';
                if (in_array('news',$class->template)) $plugin['types'][] = 'news';
            }
        }
        
        // Check for events
        if ( (@isset($class->events)) && (!empty($class->events)) ) {
            $plugin['types'][] = 'event';
            
            $eventcpu = load_cpu('event');
            foreach ($class->events as $event) {
                if ($e = $eventcpu->get($event)) {
                    $e['plugins'][] = $plugin['name'];
                    $eventcpu->edit($event,$e);
                } else $eventcpu->add(array('name'=>$event,'plugins'=>array($plugin['name'])));
            }
            
            // Merge messages
            $eventcpu->messages['good'] = array(); // Clear confirmation
            $this->merge($eventcpu);
            
            $this->debug('Applied events: ' . var_export($class->events,true));
        }
        
        // All done, install plugin
        if (makepath('.../lib/plugins/' . $plugin['name'] . '.php') != makepath($file->uri) ) {
            $copy = load_file('.../lib/plugins/' . $plugin['name'] . '.php');
            $copy->copy($file->uri);
        }
        
        return parent::add($plugin);
    }
    
}
?>