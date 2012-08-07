<?php
import('.../lib/cpu/lib.php');

class cpu_tmp extends cpu_lib {
    var $type = 'tmp';
    
    var $db=false;
    function cpu_tmp() {$this->db = load_db('tmp');}
    
    // Validation rules
    var $required = array();
    
    
    function validate($data) {return $data;}
    
    
    
    function get($uri) {
        $item = $this->db->get($uri,true);
        if (!$item) return $this->error($this->type,'badid');
        
        return $item;
    }
    
    
    
    function add($data) {
        $data = $this->start_event('add',$data);
        
        // Store changes
        $item = $this->db->save($data);
        $item = $this->get($item);
        
        // Return good
        $this->end_event('add',$item);
        $this->good($this->type,'add');
        return $item;
    }
    
    
    
    function edit($id,$data) {
        $data = $this->start_event('edit',$data);
        
        // Store changes
        $item = $this->db->save($data,$id);
        $item = $this->get($item);
        
        // Return good
        $this->end_event('edit',$item);
        $this->good($this->type,'edit');
        return $item;
    }
}
?>