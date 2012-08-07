<?php
import('.../lib/cpu/lib.php');

class cpu_template extends cpu_lib {
    var $type = 'template';
    
    var $db=false;
    function cpu_template() {$this->db = load_db('template');}
    
    // Validation rules
    var $required = array(
        'contenttype'=>3,
        'vars'=>'array',
    );
    
    
    
    function validate($fileid,$data,$new=false) {
        // Check page
        $filedb = load_db('file');
        $file = $filedb->get($fileid);
        if (!parse_dbresult($file)) return $this->error($this->type,'badfile');
        
        if (isset($data['content'])) $data['content'] = templatetext('store',$data['content']);
        
        return parent::validate($data,$new);
    }
    
    
    
    function get($page,$id=false) {
        // Load item
        $item = $this->db->get($page,$id);
        if (!parse_dbresult($item)) {
            $this->error($this->type,'badid');
            return false;
        }
        
        return $this->format($item);
    }
    
    
    
    
    function get_globals() {
        // Load item
        $item = $this->db->get_globals();
        if (!parse_dbresult($item)) {
            $this->error($this->type,'badglobal');
            return false;
        }
        
        return $item;
    }
    function edit_globals($data) {
        // Load original item
        $item = $this->get_globals();
        
        // Create item & validate
        foreach ($item as $type => $arr) $item[$type] = array_merge($arr,$data[$type]);
        $data = $this->start_event('edit_globals',$data,$item);
        
        $this->db->save_globals($item);
        
        // Return good
        $this->end_event('edit_globals',$item);
        $this->good($this->type,'edit_globals_good');
        return true;
    }
    
    
    
    function add($fileid,$data) {
        // Create item & validate
        $this->start_event('add',$fileid,$data);
        if (!$item = $this->validate($fileid,$data,true)) return false;
        
        // Store changes
        $item['id'] = $this->db->save($item,$fileid);
        
        // Add new template to page
        $filedb = load_db('file');
        $file = $filedb->get($fileid);
        $file['templates'][] = array(
            'name'=>(isset($item['name'])?$item['name']:$item['id']),
            'trigger'=>'',
        );
        $file['order'][] = $item['id'];
        $filedb->save($file,$file['name']);
        
        $item = $this->db->get($file['name'],$item['id']);
        
        
        $filecpu = load_cpu('file');
        $filecpu->rebuild($filecpu->get($file['name']));
        
        // Return good
        $this->end_event('add',$fileid,$item);
        $this->good('page','add_good');
        return $item;
    }
    
    
    
    function edit($fileid,$id,$data) {
        // Load original item
        $item = $this->db->get($fileid,$id);
        if (!parse_dbresult($item)) {
            $this->error($this->type,'badid');
            return false;
        }
        
        // Create item & validate
        $data = array_merge($item,$data);
        $data = $this->start_event('edit',$data,$item);
        if (!$item = $this->validate($fileid,$data)) return false;
        
        // Store changes
        $item['id'] = $this->db->save($item,$fileid,$id);
        
        // Return good
        $this->end_event('edit',$fileid,$item);
        $this->good('page','edit_good');
        return $item;
    }
    
    
    
    function delete($fileid,$id) {
        // Load original item
        $item = $this->get($fileid,$id);
        if (!$item) return $this->error($this->type,'badid');
        
        $item = $this->start_event('delete',$fileid,$item);
        
        // Delete
        $this->db->delete($fileid,$id);
        
        
        // Load containing page
        $filedb = load_db('file');
        $file = $filedb->get($fileid);
        
        // Remove template for page data
        unset($file['templates'][$id]);
        $i = $file['order'][($id-1)];
        unset($file['order'][($id-1)]);
        foreach ($file['order'] as $k => $o) {
            if ($o>$i) $file['order'][$k]--;
        }
        $filedb->save($file,$file['name']);
        
        // Re-ID shifted templates
        foreach ($file['templates'] as $k => $t) {
            if ($k>$id) {
                $f = $this->db->templatefile($file['name'],$k);
                $f->rename(filesafe(str_replace('/','.',$file['name'])).'.'.filesafe(($k-1)).'.tpl');
            }
        }
        
        $filecpu = load_cpu('file');
        $filecpu->rebuild($filecpu->get($file['name']));
        
        // Return good
        $this->end_event('delete',$fileid,$item);
        $this->good('page','delete_good');
        return $item;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete certain string from all template pages
    ////////////////////////////////////////////////////////////
	function delete_content($trigger) {
		$list = $this->get_content($trigger);
		foreach ($list as $page) {
			$this->edit($page['fileid'],$page['id'],$page);
		}
        return true;
    }
	
	function get_content($trigger) {
		$return = array();
		$filecpu = load_cpu('file');
        $files = $filecpu->get();
        foreach ($files as $file) {
            $pages = $this->get($file['name']);
            foreach ($pages as $page) {
                if (strpos(" ".$page['content'],$trigger)) {
					$page['fileid'] = $file['name'];
					$return[] = $page;
                }
            }
        }
        return $return;
	}
}
?>