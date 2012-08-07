<?php
import('.../lib/db/file/idbase.php');

function cmp_timestamp($a, $b) {return ($a['timestamp']==$b['timestamp']?strcmp($a['type'],$b['type']):($a['timestamp']<$b['timestamp']?-1:1));}

class db_cron extends db_flib_idbase {
    // Cron files layout
    var $layout = array(
        'timestamp'=>'int',
        'type'=>'string',
        'function'=>'string',
        'data'=>'storedarray',
        'files'=>'storedarray',
    );
    
    // db_flib_idbase info
    var $perfile = 20;
    var $filepath = 'tmp/cron/';
    
    
    
    function save($cron,$id=false) {
        if ($id) {
            $original = $this->get($id);
            if (!parse_dbresult($original)) return array('result'=>'error');
        }
        
        // Save temp files
        if ( (isset($cron['files'])) && (is_array($cron['files'])) ) {
            // Load original for comparison
            if ($id) {
                $tmpdb = load_db('tmp');
                foreach ($cron['files'] as $key => $file) {
                    if (isset($original['files'][$key])) {
                        // Overwrite old file
                        $cron['files'][$key]['tmp_name'] = 'tmp:///'.$tmpdb->save($file['tmp_name'],str_replace('tmp:///','',$original['files'][$key]['tmp_name']));
                        unset($original['files'][$key]);
                        
                    // Create new file
                    } else $cron['files'][$key]['tmp_name'] = 'tmp:///'.$tmpdb->save($file['tmp_name']);
                }
                
                // Cleanup unused old files
                if ($original['files']) {
                    foreach ($original['files'] as $file) $tmpdb->delete(str_replace('tmp:///','',$file['tmp_name']));
                }
            } elseif ($cron['files']) {
                $tmpdb = load_db('tmp');
                foreach ($cron['files'] as $key => $file) {
                    $cron['files'][$key]['tmp_name'] = 'tmp:///'.$tmpdb->save($file['tmp_name']);
                }
            }
        }
        
        $id = parent::save($cron,$id);
        
        $oldalarm = $this->getalarm();
        if ( ( (isset($original)) && ($original['timestamp']==$oldalarm) ) || ($cron['timestamp'] < $oldalarm) ) $this->setalarm();
        return $id;
    }
    
    function delete($id,$execute=false) {
        // Delete files
        $cron = $this->get($id);
        if ( (parse_dbresult($cron)) && ($cron['files']) && (!$execute) ) {
            foreach ($cron['files'] as $key => $file) {
                $file = load_file($file['tmp_name']);
                $file->delete();
            }
        }
        
        // Always hard delete
        $file = $this->itemfile($id);
        $arr = $file->read('array');
        unset($arr[($id-$file->data['firstid'])]);
        if (empty($arr)) {
            $file->delete();
        } else $file->write($arr);
        
        $this->clearcache();
        
        // Set alarm unless told otherwise
        if ( (!$execute) && ($cron['timestamp']==$this->getalarm()) ) $this->setalarm();
    }
    
    
    
    // Returns jobs that need to be executed and deletes them
    function executelist() {
        $date = date('U');
        // Check cache of next timestamp
        $file=false;
        $id=0;
        $result = array();
        $allcron = $this->get();
        $deleted = array();
        foreach ($allcron as $cron) {
            if ($cron['timestamp']<=$date) {
                $result[] = $cron;
                
                // Delete cron fixing hard-delete problems
                foreach ($deleted as $id) {
                    if ($id <= $cron['id']) $cron['id']--;
                }
                $this->delete($cron['id'],true);
                $deleted[] = $cron['id'];
            }
        }
        $this->setalarm();
        
        // Return jobs
        if (!$result['result']) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    
    function getalarm() {
        $file = $this->getfile($this->filepath.'alarm');
        return rtrim($file->read('string'));
    }
    function setalarm() {
        $allcron = $this->get();
        parse_dbresult($allcron);
        
        $l = 9999999999;
        foreach ($allcron as $cron) {
            if ($cron['timestamp'] < $l) $l = $cron['timestamp'];
        }
        
        $file = $this->getfile($this->filepath.'alarm');
        $file->write($l);
    }
    function disablealarm() {
        $file = $this->getfile($this->filepath.'alarm');
        $file->write('9999999999');
    }
}
?>