<?php
import('.../lib/cpu/lib.php');

class cpu_tag extends cpu_lib {
    var $type = 'tag';
    
    var $db=false;
    function cpu_tag() {$this->db = load_db('tag');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'tags'=>'array',
    );
    
    
    
}
?>