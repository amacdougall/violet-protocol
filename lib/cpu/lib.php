<?php

class cpu_lib {
    var $messages = array(
        'error'=>array(),
        'warning'=>array(),
        'good'=>array(),
        'debug'=>array(),
    );
    
    // Messages
    function error($lang,$code,$extra=false) {$this->messages['error'][] = get_lang($lang,$code,$extra);}
    function warning($lang,$code,$extra=false) {$this->messages['warning'][] = get_lang($lang,$code,$extra);}
    function good($lang,$code,$extra=false) {$this->messages['good'][] = get_lang($lang,$code,$extra);}
    function debug($message) {$this->messages['debug'][] = $message;}
    function merge($cpu) {foreach ($cpu->messages as $type => $array) $this->messages[$type] += $array;}
    
    
    
    function start_event($event,$item) {
        $args = func_get_args();
        $event = array_shift($args);
        
        $event = 'on_'.$this->type.'_'.$event;
        
        $eventdb = load_db('event');
        $data = $eventdb->get($event);
        if (parse_dbresult($data)) {
            foreach ($data['plugins'] as $plugin) {
                $class = load_plugin($plugin);
                
                $function = false;
                if (method_exists($class,$event)) {
                    $function = $event;
                } elseif (method_exists($class,'on_other')) {
                    $function = 'on_other';
                    array_unshift($args,$event);
                } else {
                    $this->warning('plugin','plugindie',$plugin);
                }
                
                if ($function) {
                    // No call_user_func_array() for classes (PHP 4)
                    // So here's an ugly hack
                    eval('$result = $class->$function($args['.implode('],$args[',array_keys($args)).']);');
                    if (parse_dbresult($result)) {
                        $this->debug('Setting event return to what ' . $plugin['id'] . ' gave');
                        $item = $result;
                    }
                }
            }
        }
        return $item;
    }
    function end_event($event,$item) {
        $args = func_get_args();
        $event = array_shift($args);
        
        $event = 'after_'.$this->type.'_'.$event;
        
        $eventdb = load_db('event');
        $data = $eventdb->get($event);
        if (parse_dbresult($data)) {
            foreach ($data['plugins'] as $plugin) {
                $class = load_plugin($plugin);
                
                $function = false;
                if (method_exists($class,$event)) {
                    $function = $event;
                } elseif (method_exists($class,'on_other')) {
                    $function = 'on_other';
                    array_unshift($args,$event);
                } else {
                    $this->warning('plugin','plugindie',$plugin);
                }
                
                if ($function) {
                    // No call_user_func_array() for classes (PHP 4)
                    // So here's an ugly hack
                    if ($args) {
                        eval('$result = $class->$function($args['.implode('],$args[',array_keys($args)).']);');
                    } else $result = $class->$function();
                    parse_dbresult($result);
                }
            }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    function validate($item,$new=false) {
        // Sift through required variables
        foreach ($this->required as $attr => $req) {
            if ( ($new) && (!isset($item[$attr])) ) return $this->error($this->type,'no_'.$attr);
            if ($req === 'array') {
                if (!is_array($item[$attr])) return $this->error($this->type,'array_'.$attr);
            } elseif (is_numeric($req)) {
                if ( ($req>0) && (strlen($item[$attr]) < $req) ) return $this->error($this->type,'short_'.$attr);
                if ( ($req<0) && (strlen($item[$attr]) > $req) ) return $this->error($this->type,'long_'.$attr);
            } elseif ( (!isset($item[$attr])) || ((is_array($item[$attr])?count($item[$attr]):strlen($item[$attr]))==0) ) {
                return $this->error($this->type,'no'.$attr);
            }
        }
        
        if ( ($new) && ($this->db->basetype == 'name') ) {// Name based
            $search = $this->search($item['name'],'name');
            if (!empty($search)) return $this->error($this->type,'dup_name');
        }
        
        // Stop user corruptions
        if ( (isset($item['author'])) && (is_array($item['author'])) && (isset($item['author']['id'])) ) $item['author'] = $item['author']['id'];
        
        return $item;
    }
    
    
    
    
    
    
    function format($item,$merge=false) {
        if ($merge) $item = array_merge($merge,$item);
        
        // Add specials
        if (isset($item['email'])) $item['gravatar'] = 'http://www.gravatar.com/avatar/'.md5(strtolower($item['email']?$item['email']:'example@example.com')).'?d='.urlencode(get_config('baseurl').'admin/img/blank.gif');
        
        return $item;
    }
    
    
    
    function get() {
        // Load item
        if ($this->db->basetype == 'name') {// Name based
            $args = func_get_args();
            $item = $this->db->get((isset($args[0])?$args[0]:false),(isset($args[1])?$args[1]:false));
        } else {// ID based
            $item = $this->db->get(func_get_args());
        }
        $i = $item;
        if ( (!parse_dbresult($item)) && ($i['result']=='error') ) {
            if ( ($this->db->basetype == 'name') || ($this->db->firstid() > 0) ) {
                $this->error($this->type,'badid');
            }
            return false;
        }
        
        return $this->format($item);
    }
    
    
    
    function search($find,$attribute=false,$partial=false) {
        // Load items
        $item = $this->db->search($find,$attribute,$partial);
        if (!parse_dbresult($item)) {
            $this->debug('Empty search');
            return array();
        }
        
        return $item;
    }
    
    
    
    function add($data) {
        // Create item & validate
        $data = $this->start_event('add',$data);
        if (!$item = $this->validate($data,true)) return false;
        
        // Store changes
        $item['id'] = $this->db->save($item);
        $item = $this->get($item['id']);
        
        // Return good
        $this->end_event('add',$item);
        $this->good($this->type,'add_good');
        return $item;
    }
    
    
    
    function edit($id,$data) {
        // Load original item
        $item = $this->get($id);
        if (!$item) return $this->error($this->type,'badid');
        
        // Create item & validate
        $data = array_merge($item,$data);
        $data = $this->start_event('edit',$data,$item);
        if (!$item = $this->validate($data)) return false;
        
        // Store changes
        $item['id'] = $this->db->save($item,$id);
        
        // Return good
        $this->end_event('edit',$item);
        $this->good($this->type,'edit_good');
        return $item;
    }
    
    
    
    function delete($id,$extra=false) {
        // Load original item
        $item = $this->get($id);
        if (!$item) return $this->error($this->type,'badid');
        
        $item = $this->start_event('delete',$item);
        
        // Delete
        $this->db->delete($id,$extra);
        
        // Return good
        $this->end_event('delete',$item);
        $this->good($this->type,'delete_good');
        return $item;
    }
    
    function firstid() {
        if ($this->db->basetype == 'id') {
            return $this->db->firstid();
        } else return false;
    }
    
    function lastid() {
        if ($this->db->basetype == 'id') {
            return $this->db->lastid();
        } else return false;
    }
}
?>