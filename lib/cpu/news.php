<?php
import('.../lib/cpu/lib.php');

class cpu_news extends cpu_lib {
    var $type = 'news';
    
    var $db=false;
    function cpu_news() {$this->db = load_db('news');}
    
    // Validation rules
    var $required = array(
        'timestamp'=>true,
        'author'=>true,
        'post'=>2,
    );
    
    
    
    function validate($item,$new=false) {
        if (!$item = parent::validate($item,$new)) return $item;
        
        // Check when editing the timestamp isn't too large
        if ( (!$new) && ($item['timestamp'] > date('U')) ) return $this->error($this->type,'late_timestamp');
        
        return $item;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add news
    ////////////////////////////////////////////////////////////
    function add($news) {
        $news = $this->start_event('add',$news);
        
        // Apply user
        if (!isset($news['author'])) {
            $authcpu = load_cpu('auth');
            $news['author'] = $authcpu->get('user','id');
        }
        
        // Apply date if needed
        if (!isset($news['timestamp'])) $news['timestamp'] = date('U');
        if ($news['timestamp'] == 0) $news['timestamp'] = 9999999999;
        
        // Check for future date
        if ($news['timestamp'] > date('U')) {
            $this->debug('Saving news as cron job');
            
            if ($news['timestamp'] != 9999999999) {
                // Check post is valid before cronning
                if (!$news = $this->validate($news,true)) return false;
            }
            
            // Save as cron
            $croncpu = load_cpu('cron');
            $news['cron'] = $croncpu->add(array(
                'timestamp'=>$news['timestamp'],
                'type'=>'news',
                'function'=>'add',
                'data'=>$news,
            ));
            
            // Merge messages
            $croncpu->messages['good'] = array(); // Clear confirmation
            $this->merge($croncpu);
            if (!$news['cron']) return false;
            
            // All good
            $this->debug('News post is now cron job #'.$news['cron']['id']);
            $news['id'] = 'cron';
            
            if ($news['timestamp'] == 9999999999) {
                $this->good($this->type,'draft_good');
            } else $this->good($this->type,'cron_good');
            
            return $news;
        }
        
        // Add news
        if (!$news = parent::add($news)) return $news;
        
        // Apply news to recent comic
        $comiccpu = load_cpu('comic');
        if ($comic = $comiccpu->get('-0')) {
            $prev = $comiccpu->get('-1');
            if ( ($prev) && ($comic['news'] == $prev['news']) ) {
                // New news set
                $comic['news'] = array($news['id']);
            } else {
                // Extend current set
                $comic['news'][] = $news['id'];
            }
            
            $comiccpu->edit($comic['id'],$comic);
        }
        
        // Merge good/bad messages
        $this->merge($comiccpu);
        
        return $news;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete news
    ////////////////////////////////////////////////////////////
    function delete($id) {
        if (!$news = parent::delete($id)) return false;
        
        // Delete news from comics
        $comiccpu = load_cpu('comic');
        $comics = $comiccpu->search($news['id'],'news');
        ksort($comics);
        $last = false;
        foreach ($comics as $comic) {
            unset($comic['news'][array_search($news['id'],$comic['news'])]);
            
            // Empty news sets, inherit from last comic
            if (empty($comic['news'])) {
                if (!$last) {
                    $last = $comiccpu->get('batch',$comic['id'],-1);
                    $last = $last[min(array_keys($last))];
                }
                $comic['news'] = $last['news'];
            }
            
            $comiccpu->edit($comic['id'],$comic);
            $last = $comic;
        }
        
        // Merge messages
        $this->merge($comiccpu);
        
        return $news;
    }
}