<?php
import('.../lib/cpu/lib.php');

class cpu_file extends cpu_lib {
    var $type = 'file';
    
    var $db=false;
    function cpu_file() {$this->db = load_db('file');}
    
    // Validation rules
    var $required = array(
        'name'=>2,
        'order'=>'array',
        'templates'=>'array',
    );
    
    
    
    function add($file) {
        if (!isset($file['order'])) $file['order'] = array();
        if (!isset($file['templates'])) $file['templates'] = array();
        
        if (strpos($file['name'],'.php')) $file['name'] = substr($file['name'],0,-4);
        if (!pathsafe('.../'.$file['name'])) return $this->error('template','badpath');
        if (strpos($file['name'],'download')) return $this->error('template','badname');
        
        $path = substr(absolute_path('.../'.$file['name']),strlen(get_config('basepath')));
        if ( ($path) && (substr($path,0,3) != 'inc') && (substr($path,0,3) != 'img') ) return $this->error('template','baddir');
        
        if (!$r = parent::add($file)) return false;
        $this->rebuild($file);
        return $r;
    }
    
    function edit($fileid,$file) {
        if (!$file = parent::edit($fileid,$file)) return false;
        $this->rebuild($file);
        return $file;
    }
    
    function delete($fileid) {
        $file = $this->get($fileid);
        if (!empty($file['order'])) return $this->error('template','filepageleft');
        
        if (!$r = parent::delete($fileid)) return false;
        if ($write = load_file('.../' . $file['name'] . '.php')) $write->delete();
        return $r;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Rebuild template file
    ////////////////////////////////////////////////////////////
    function rebuild($file=false) {
        if (!$file) {
            $files = $this->get();
            foreach ($files as $file) $this->rebuild($file);
            return;
        }
        
        $content = '<?php' . "\n"
        . '// ComicCMS :: Copyright (C) 2007 Steve H'. "\n"
        . '// http://comiccms.com/'. "\n"
        . '// File auto-generated '.date('c'). "\n"
        . 'if (!@include_once(\'' . get_config('basepath') . 'lib/lib.php\')) die(\'ComicCMS library file could not be loaded!\');' . "\n"
        . 'import(\'.../lib/site/page.php\');';
        
        $templatecpu = load_cpu('template');
        foreach ($file['order'] as $key) if ($template = $templatecpu->get($file['name'],$key)) {
            $show = 'show_templatepage('.var_export($file['name'],true).','.$key.',' . str_replace(
                array("\n"," "),'',
                var_export(array('s'=>$template['source'],'e'=>($template['encase']?1:0),'c'=>$template['contenttype']),true)
            ) . ');';
            
            $trigger = $file['templates'][$key]['trigger'];
            
            if (!$trigger) {
                $content .= "\n".$show;
            } elseif (strpos($trigger,'='))  {
                $content .= "\n".'if ( ($v = cvar('.var_export(substr($trigger,0,strpos($trigger,'=')),true).')) && ($v == '.var_export(substr($trigger,strpos($trigger,'=')+1),true).') ) '.$show;
            } elseif (strpos($trigger,'>')) {
                $content .= "\n".'if ( ($v = cvar('.var_export(substr($trigger,0,strpos($trigger,'>')),true).')) && ($v > '.(substr($trigger,strpos($trigger,'>')+1)*1).') ) '.$show;
            } elseif (strpos($trigger,'<')) {
                $content .= "\n".'if ( ($v = cvar('.var_export(substr($trigger,0,strpos($trigger,'<')),true).')) && ($v < '.(substr($trigger,strpos($trigger,'<')+1)*1).') ) '.$show;
            } elseif (substr($trigger,0,1)=='!') {
                $content .= "\n".'if (!cvar('.var_export(substr($trigger,1),true).')) '.$show;
            } else {
                $content .= "\n".'if (cvar('.var_export($trigger,true).')) '.$show;
            }
        }
        $content .= "\n".'die(\'All triggers missed\');'."\n".'?'.'>';
        
        $write = load_file('.../' . $file['name'] . '.php');
        $write->make_directory();
        $write->write($content,755);
    }
    
}

?>