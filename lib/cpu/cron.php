<?php
import('.../lib/cpu/lib.php');

class cpu_cron extends cpu_lib {
    var $type = 'cron';
    
    var $db=false;
    function cpu_cron() {$this->db = load_db('cron');}
    
    // Validation rules
    var $required = array(
        'timestamp'=>true,
        'type'=>true,
    );
    
    
    
    function execute() {
        // Prevent double-add
        $crondb = load_db('cron');
        $crondb->disablealarm();
        
        // Fetch and delete tasks
        $items = $this->db->executelist();
        if (!parse_dbresult($items)) return $this->debug('Empty executable cron list');
        
        // Order tasks
        $times = array();
        foreach ($items as $k => $item) {
            if (!isset($times[$item['timestamp']])) {
                $times[$item['timestamp']] = array(&$items[$k]);
            } elseif ($item['type'] == 'comic') {// Comics before news
                array_unshift($times[$item['timestamp']],&$items[$k]);
            } else {
                array_push($times[$item['timestamp']],&$items[$k]);
            }
        }
        
        // Execute Tasks
        foreach ($times as $is) foreach ($is as $item) {
            if (!isset($item['data']['timestamp'])) $item['data']['timestamp'] = $item['timestamp'];
            switch ($item['type']) {
                case 'comic': case 'news':
                    $cpu = load_cpu($item['type']);
                    $cpu->add($item['data'],$item['files']);
                    
                    // Remove tmp files
                    foreach ($item['files'] as $file) {
                        $file = load_file($file['tmp_name']);
                        $file->delete();
                    }
                    break;
                
                default:
                    break;
            }
        }
    }
    
    
}
?>