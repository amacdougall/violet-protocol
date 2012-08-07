<?php
import('.../lib/cpu/lib.php');

class cpu_event extends cpu_lib {
    var $type = 'event';
    
    var $db=false;
    function cpu_event() {$this->db = load_db('event');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'plugins'=>'array',
    );
    
    
    
}
?>