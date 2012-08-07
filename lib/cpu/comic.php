<?php
import('.../lib/cpu/lib.php');

class cpu_comic extends cpu_lib {
    var $type = 'comic';
    
    var $db=false;
    function cpu_comic() {$this->db = load_db('comic');}
    
    // Validation rules
    var $required = array(
        'timestamp'=>true,
        'author'=>true,
        'ext'=>2,
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Validate uploaded image
    ////////////////////////////////////////////////////////////
    function comicimg($img) {
        // Find file
        if (isset($img['comicimg'])) $img = $img['comicimg'];
        if ( (!isset($img['tmp_name'])) || (!$img['tmp_name']) ) {
            $this->error($this->type,'noimg');
            return false;
        }
        
        // Inspect file
        if (!$img['ext'] = substr($img['name'],(strrpos($img['name'],'.')+1))) {
            $this->error($this->type,'noext');
            return false;
        }
        
        // Validate type
        if (array_search(strtolower($img['ext']),get_config('comicexts'))===false) {
            $this->error($this->type,'badext',$img['ext']);
            return false;
        }
        
        // Get size
        if (substr($img['tmp_name'],0,7)!='tmp:///') {
            $info = getimagesize(makepath($img['tmp_name']));
            $img['width'] = ($info?$info[0]:'');
            $img['height'] = ($info?$info[1]:'');
        }
        
        // All good
        return $img;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add comic
    ////////////////////////////////////////////////////////////
    function add($comic,$files) {
        $comic = $this->start_event('add',$comic,$files);
        
        // Apply user
        if (!isset($comic['author'])) {
            $authcpu = load_cpu('auth');
            $comic['author'] = $authcpu->get('user','id');
        }
        
        // Validate image
        if (!$img = $this->comicimg($files)) return false;
        foreach (array('ext','width','height') as $a) $comic[$a] = $img[$a];
        
        // Check for future date
        if (!isset($comic['timestamp'])) $comic['timestamp'] = date('U');
        if ($comic['timestamp'] == 0) $comic['timestamp'] = 9999999999;
        if ($comic['timestamp'] > date('U')) {
            $this->debug('Saving comic as cron job');
            
            if ($comic['timestamp'] != 9999999999) {
                // Check comic is valid before cronning
                if (!$comic = $this->validate($comic,true)) return false;
            }
            
            // Save as cron
            $croncpu = load_cpu('cron');
            $comic['cron'] = $croncpu->add(array(
                'timestamp'=>$comic['timestamp'],
                'type'=>'comic',
                'function'=>'add',
                'data'=>$comic,
                'files'=>array('comicimg'=>$img),
            ));
            
            // Merge messages
            $croncpu->messages['good'] = array(); // Clear confirmation
            $this->merge($croncpu);
            if (!$comic['cron']) return false;
            
            // All good
            $this->debug('Comic is now cron job #'.$comic['cron']['id']);
            $comic['id'] = 'cron';
            
            if ($comic['timestamp'] == 9999999999) {
                $this->good($this->type,'draft_good');
            } else $this->good($this->type,'cron_good');
            
            return $comic;
        }
        
        // Link news posts
        $prev = $this->db->get('-0');
        if (parse_dbresult($prev)) {
            $comic['news'] = $prev['news'];
        } else {
            $this->debug('Last comic not found, attaching all news posts');
            $newsdb = load_db('news');
            if ($newsdb->lastid()) {
                $comic['news'] = range($newsdb->firstid(),$newsdb->lastid());
            }
        }
        
        // Add comic
        if ($comic = parent::add($comic)) {
            // Save image
            if (substr($img['tmp_name'],0,7)=='tmp:///') {
                $tmpdb = load_db('tmp');
                $file = $tmpdb->get($img['tmp_name']);
                $img['tmp_name'] = $file->uri;
            }
            $this->db->saveimg($img['tmp_name'],$comic['id'].'.'.$img['ext']);
            $this->admin_thumbnail($comic['id'],$comic['id'].'.'.$img['ext']);
            
            $comic['uploaded'] = true;
            return $comic;
        } else return false;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit comic
    ////////////////////////////////////////////////////////////
    function edit($id,$data,$files=false) {
        // Edit comic image
        if ($files) {
            $comic = $data;
            
            // Save new image
            if (!$img = $this->comicimg($files)) return false;
            $this->db->saveimg($img['tmp_name'],$comic['id'].'.'.$img['ext']);
            $this->admin_thumbnail($comic['id'],$comic['id'].'.'.$img['ext']);
            foreach (array('ext','width','height') as $a) $comic[$a] = $img[$a];
            
            // Save changes
            if ($data != $comic) {
                $this->debug('Editing comic database');
                
                // Delete old file
                if ( (!isset($data['ext'])) || ($img['ext'] != $data['ext']) ) {
                    if (!$this->db->deleteimg($comic['id'].'.'.$data['ext'])) $this->debug('Old comic image not found for deletion');
                }
                
                $data = $comic;
            }
        }
        
        // Save comic data
        return parent::edit($id,$data);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete comic
    ////////////////////////////////////////////////////////////
    function delete($id) {
        if (!$comic = parent::delete($id)) return false;
        
        // Delete image
        if (!$this->db->deleteimg($comic['id'].'.'.$comic['ext'])) $this->debug('Comic image not found for deletion');
        $this->admin_thumbnail($comic['id'],false);
        
        // Fix orphaned news
        if ($comic['isfirst']) {
            $next = $this->db->get('+1');
            if (parse_dbresult($next)) {
                $next = reset($next);
                if ( ($next) && (min($next['news']) != min($comic['news'])) ) {
                    $this->edit(array('news'=>range(min($comic['news']),max($news['news']))),$next['id']);
                }
            }
        } else {
            $last = $this->db->get('batch',$comic['id'],-1);
            if (parse_dbresult($last)) {
                $last = reset($last);
                if ( ($last['news']) && ( (max($last['news']) != max($comic['news'])) ) ) {
                    $this->edit($last['id'],array('news'=>range(min($last['news']),max($comic['news']))));
                }
            }
        }
        
        return $comic;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate thumbnail for admin panel
    ////////////////////////////////////////////////////////////
    function admin_thumbnail($id,$file) {
        if ( ($file) && (function_exists('gd_info')) ) {
            $img = load_class('image');
            if ($img->load('.../img/comic/'.$file)) {
                if ($img->width >= $img->height) {
                    $img->resize(200);
                } else {
                    $img->resize('inherit',200);
                }
                
                $img->render_jpeg('.../admin/img/thumb/'.$id.'.jpg',60);
            } else {
                $img = load_file('.../admin/img/thumb/'.$id.'.jpg');
                $img->copy('.../admin/img/nothumb.jpg');
                $img->close();
            }
        }
        
        if ( (!$file) && ($file = load_file('.../admin/img/thumb/'.$id.'.jpg',true)) ) $file->delete();
    }
    
}
?>