<?php
import('.../lib/admin/lib.php');

class admin_plugin extends admin_lib {
    var $section = 'plugin';
    var $navigation = array(
        'add'=>1,
        'use'=>'use',
        'edit'=>'use|1',
        'delete'=>'use|1',
        //'event'=>1,
    );
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Validate and retreive requested plugin
    ////////////////////////////////////////////////////////////
    function v_pluginid(&$pluginid) {
        if (!setvar($pluginid,'g','plugin')) return $this->warning('pluginnoid');
        
        if (!$plugin = $this->cpu('plugin','get',$pluginid)) return $this->error('pluginbadid');
        
        $class = load_plugin($plugin);
        
        return $class;
    }
    
    
    
    
    function d_main($offset=false) {
        $this->newpage('main');
        
        // Fetch plugins
        if (!$plugins = plaintext($this->cpu('plugin','get'))) return false;
        
        // Display plugins
        foreach($plugins as $id => $plugin) if ($plugin['name'] != 'auto_regen') {
            $this->addline('<div style="float:left; width:32%; margin:10px 0.5%;">');
            $this->addline('<h2 style="text-align:left;">'.$plugin['title'].'</h2>');
            $this->addline('<ul class="editlist" style="margin-left:35px;">');
            $this->addline('    <li><a href="?p=plugin:use&amp;plugin='.$plugin['name'].'">'.get_lang('title','pluginuse').'</a></li>');
            $this->addline('    <li><a href="?p=plugin:edit&amp;plugin='.$plugin['name'].'">'.get_lang('title','pluginedit').'</a></li>');
            $this->addline('    <li><a href="?p=plugin:delete&amp;plugin='.$plugin['name'].'" class="delete">X</a><a href="?p=plugin:delete&amp;plugin='.$plugin['name'].'">'.get_lang('title','plugindelete').'</a></li>');
            $this->addline('</ul>');
            $this->addline('</div>');
        }
    }
    
    
    
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields() {
        return array(
            'plugin'=>'file',
        );
    }
    
    
    
    function d_add($data=false) {
        $this->newpage('add');
        
        $this->warning('warning');
        
        $this->genform($this->fields(),plaintext($data),array(
            'img'=>'wizard',
            'enctype'=>'multipart/form-data',
        ));
    }
    function p_add($data=false,$files=false) {
        $files = $this->readform($data,$this->fields(),$files);
        if ($files===false) return $this->d_add();
        
        if (!$plugin = $this->cpu('plugin','add',$data,$files['plugin'])) return $this->d_add();
        
        $this->d_main();
    }
    
    
    
    
    
    
    
    
    function pass_d($function,$pluginid=false,$data=false) {
        if (!$class = $this->v_pluginid($pluginid)) return $this->d_main();
        $this->newpage($function,'plugin='.$pluginid);
        
        $this->addline('<h2>'.$class->lang('_title').'</h2>');
        $this->addline('<p style="margin-top:0px; text-align:center;">'.$class->lang('_desc').'</p>');
        
        if (method_exists($class,'d_'.$function)) {
            $f = 'd_'.$function;
            return $class->$f(&$this,$data);
        } else {
            $this->addline('<p style="text-align:center;">' . get_lang('plugin','pluginno'.$function) . '</p>');
            return 404;
        }
    }
    
    function pass_p($function,$pluginid=false,$data=false) {
        if (!$class = $this->v_pluginid($pluginid)) {
            $this->d_main();
            $this->end(); // Stop anything else executing
        }
        
        if (method_exists($class,'p_'.$function)) {
            $f = 'p_'.$function;
            $r = $class->$f(&$this,$data);
            return ($r===true?200:$r);
        } else {
            return 404;
        }
    }
    
    
    
    function d_use($pluginid=false,$data=false) {
        return $this->pass_d('use',$pluginid,$data);
    }
    function p_use($pluginid=false,$data=false) {
        $this->pass_p('use',$pluginid,$data);
    }
    
    function d_edit($pluginid=false,$data=false) {
        return $this->pass_d('edit',$pluginid,$data);
    }
    function p_edit($pluginid=false,$data=false) {
        switch ($this->pass_p('edit',$pluginid,$data)) {
            case false: // Something went wrong
                $this->d_edit($pluginid,$data);
                break;
            
            case 404: // No edit process
                $this->warning('noeditp');
                $this->d_use($pluginid);
                break;
            
            case 200: // All good
                $this->good('editdone');
            default: // Unknown
                $this->d_use($pluginid);
                break;
        }
    }
    
    function d_delete($pluginid=false,$data=false) {
        if ($this->pass_d('delete',$pluginid,$data)==404) {
            $this->warning('nodelete');
            $this->genform(array('fullremove'=>'checkbox'),array());
        }
    }
    function p_delete($pluginid=false,$data=false) {
        switch ($this->pass_p('delete',$pluginid,$data)) {
            case false: // Something went wrong
                $this->d_delete($pluginid,$data);
                break;
            
            case 404: // Force deletion
                if (!$this->readform($data,array('fullremove'=>'checkbox'))) return false;
                $class = $this->v_pluginid($pluginid);
                $class->delete($data['fullremove']);
                break;
            
            case 200: // All good
                $this->good('deletedone');
            default: // Unknown
                $this->d_main();
                break;
        }
    }
    
    
    
    
}

?>