<?php
import('.../lib/admin/template.php');

class admin_page extends admin_template {
    var $navigation = array(
        'page:add'=>1,
        'page:edit'=>'page:edit|edit',
        'page:delete'=>'page:edit|edit',
    );
    var $lang = 'page';
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch,$extra=false) {
        switch($switch) {
            case 'add':
                $r = array(
                    'name'=>'text',
                    'type'=>new select_input,
                );
                $r['type']->add('html',get_lang('page','templatehtml'),true);
                $r['type']->add('css',get_lang('page','templatecss'));
                $r['type']->add('js',get_lang('page','templatejs'));
                $r['type']->add('rss',get_lang('page','templaterss'));
                return $r;
                break;
                
            case 'edit':
                $r = array(
                    'name'=>'text',
                    array('type'=>'html','html'=>'<input type="submit" value="'.get_lang('page','savereturn').'" class="savereload" name="save" />'),
                    'content'=>array('type'=>'textarea','class'=>'resizeTextarea','rows'=>10),
                    array('type'=>'html','html'=>'<input type="submit" value="'.get_lang('page','savereturn').'" class="savereload" name="save" />'),
                );
                if ($extra) {
                    $r[] = array('type'=>'html','html'=>'<h2>'.get_lang('page','globalvars').'</h2>');
                    $r[] = array('type'=>'html','html'=>'<p>'.get_lang('page','globalvarsintro').'</p>');
                    $globals = $this->cpu('template','get_globals');
                    $vars = plaintext(array_keys(array_merge($globals['header']['vars'],$globals['footer']['vars'])));
                    foreach ($vars as $var) {
                        $r['vars_'.$var] = array('type'=>'text','title'=>'{{'.$var.'}}');
                    }
                }
                
                return $r;
                break;
            
            case 'delete':
                return array(
                
                );
                break;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Validate and retreive requested template
    ////////////////////////////////////////////////////////////
    function v_pageid(&$fileid,&$pageid,$data=array()) {
        if (!setvar($fileid,'g','file')) return $this->warning('filenoid');
        if (!setvar($pageid,'g','page')) return $this->warning('pagenoid');
        
        if (!$page = $this->cpu('template','get',$fileid,$pageid)) return $this->error('pagebadid');
        
        if ($data) return $this->cpu('template','format',$data,$page);
        return $page;
    }
    
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Add Page
    ////////////////////////////////////////////////////////////
    function d_add($fileid=false,$page=false) {
        if (!$file = $this->v_fileid($fileid)) return $this->d_main();
        $this->newpage('page:add','file='.$fileid);
        
        $this->genform($this->fields('add'),plaintext($page),array(
            'img'=>'view_right',
        ));
    }
    function p_add($fileid=false,$page=false) {
        if (!$file = $this->v_fileid($fileid)) return $this->d_main();
        
        if (!$this->readform($page,$this->fields('add'))) return $this->d_add($fileid,$page);
        
        // Apply defaults
        $page['encase'] = false;
        $page['vars'] = array();
        $page['content'] = '';
        switch ($page['type']) {
            case 'html':
                $page['contenttype'] = 'text/html';
                $page['encase'] = true;
                break;
            case 'css':
                $page['contenttype'] = 'text/css';
                break;
            case 'js':
                $page['contenttype'] = 'text/javascript';
                break;
            case 'rss':
                $page['contenttype'] = 'application/rss+xml';
                break;
        }
        
        if (!$page = $this->cpu('template','add',$fileid,$page)) return $this->d_add($fileid,$page);
        
        $this->d_edit($fileid,$page['id']);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit Page
    ////////////////////////////////////////////////////////////
    function d_edit($fileid=false,$pageid=false,$page=false) {
        // Get file data
        $file=false;
        if (!$file = plaintext($this->v_fileid($fileid,$file))) return $this->d_main();
        
        // Page data
        if (!$page = $this->v_pageid($fileid,$pageid,$page)) return $this->d_main();
        $this->newpage('page:edit','file='.$fileid.'&amp;page='.$pageid);
        
        // Unpack vars for form
        if ($page['encase']) {
            foreach ($page['vars'] as $k => $v) $page['vars_'.$k] = $v;
        }
        $page['name'] = $file['templates'][$pageid]['name'];
        
        $this->addline('<h2>'.$file['name'].'.php :: '.$file['templates'][$pageid]['name'].'</h2>');
        $this->genform($this->fields('edit',$page['encase']),$page,array(
            'img'=>'view_sidetree',
        ));
    }
    function p_edit($fileid=false,$pageid=false,$page=false) {
        // Get file data
        $file=false;
        if (!$oldfile = $this->v_fileid($fileid,$file)) return $this->d_main();
        
        if (!$oldpage = $this->v_pageid($fileid,$pageid,$page)) return $this->d_main();
        
        if (!$this->readform($page,$this->fields('edit',$oldpage['encase']))) return $this->d_edit($fileid,$pageid,$page);
        
        // Repack vars from form
        if ($oldpage['encase']) {
            $page['vars'] = array();
            foreach ($page as $k => $v) {
                if ( ($k) && (substr($k,0,5)=='vars_') ) $page['vars'][substr($k,5)] = $v;
            }
        }
        
        if (!$this->cpu('template','edit',$fileid,$pageid,$page)) return $this->d_edit($fileid,$pageid,$page);
        
        // Friendly name
        if ($page['name'] != $oldfile['templates'][$pageid]['name']) {
            $oldfile['templates'][$pageid]['name'] = $page['name'];
            $this->cpu('file','edit',$fileid,$oldfile);
        }
        
        if ( (isset($_POST['save'])) && ($_POST['save'] == get_lang('page','savereturn')) ) {
            return $this->d_edit($fileid,$pageid);
        }
        
        if (isset($_POST['save'])) unset($_POST['save']);
        load_admin('template','edit');
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete Page
    ////////////////////////////////////////////////////////////
    function d_delete($fileid=false,$pageid=false,$page=false) {
        // Get file data
        $file=false;
        if (!$file = plaintext($this->v_fileid($fileid,$file))) return $this->d_main();
        
        // Page data
        if (!$page = $this->v_pageid($fileid,$pageid,$page)) return $this->d_main();
        $this->newpage('page:delete','file='.$fileid.'&amp;page='.$pageid);
        
        $this->addline('<h2>'.$file['name'].'.php :: '.$file['templates'][$pageid]['name'].'</h2>');
        $this->genform($this->fields('delete'),$page,array(
            'img'=>'editdelete',
        ));
    }
    function p_delete($fileid=false,$pageid=false,$page=false) {
        if (!$oldpage = $this->v_pageid($fileid,$pageid,$page)) return $this->d_main();
        
        if (!$this->readform($page,$this->fields('delete'))) return $this->d_delete($fileid,$pageid,$page);
        
        if (!$this->cpu('template','delete',$fileid,$pageid)) return $this->d_delete($fileid,$pageid,$page);
        
        if (isset($_POST['save'])) unset($_POST['save']);
        load_admin('template','edit');
    }
    
}