<?php
import('.../lib/cpu/lib.php');

class cpu_cache extends cpu_lib {
    var $type = 'cache';
    
    var $db=false;
    function cpu_cache() {$this->db = load_db('cache');}
    
    // Validation rules
    var $required = array(
        
    );
    
    
    
    
    function get_delete($search) {
        $caches = $this->get($search);
        foreach ($caches as $cache) $this->db->delete($cache['name']);
        return true;
    }
    
    function add($data) {
        $data['name'] = $data['id'].'.'.$data['subid'].'.'.$data['flag'];
        $this->db->addfile($data['name'],$data['content'],$data['vars']);
        $this->db->addindex($data);
    }
    
    function save_globals($globals) {
        return $this->db->save_globals($globals);
    }
    
    
    function clear($data) {
        if ($data['pagecache']) {
            $this->db->delete('header');
            $this->db->delete('footer');
        }
        
        $caches = $this->get();
        foreach ($caches as $cache) {
            if ( ($data['pagecache']) && (substr($cache['name'],0,4)=='tpl_') ) $this->db->delete($cache['name']);
            if ( ($data['plugincache']) && (substr($cache['name'],0,3)=='pl_') ) $this->db->delete($cache['name']);
        }
        
        $this->good('config','cache_good');
        return true;
    }
}
?>