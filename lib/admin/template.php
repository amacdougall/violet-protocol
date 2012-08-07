<?php
import('.../lib/admin/lib.php');

class admin_template extends admin_lib {
    var $section = 'template';
    var $navigation = array(
        'add'=>1,
        'edit'=>'edit',
        'delete'=>'edit|1',
        
        'editglobal'=>1,
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch,$extra=false) {
        switch($switch) {
            case 'add':
                return array(
                    'name'=>'text',
                );
                break;
            
            case 'edit':
                $r = array(
                    'triggersort'=>array('type'=>'hidden','value'=>implode(',',$jsarray),'name'=>'input_triggersort','id'=>'triggersort','label'=>false),
                );
                foreach ($extra as $id => $template) {
                    $r['trigger_'.$id] = array('type'=>'text','title'=>$template['name']);
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
    // Validate and retreive requested page
    ////////////////////////////////////////////////////////////
    function v_fileid(&$fileid,$data=array()) {
        if (!setvar($fileid,'g','file')) return $this->warning('filenoid');
        
        if (!$file = $this->cpu('file','get',$fileid)) return $this->error('filebadid');
        
        if ($data) return $this->cpu('file','format',$data,$file);
        return $file;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Main page
    ////////////////////////////////////////////////////////////
    function d_main($offset=false) {
        $this->newpage('main');
        
        // Find template pages
        $filelist = plaintext($this->cpu('file','get'));
        
        // Display pages
        $this->addline('<a href="?p=template:editglobal" class="onenav">' . get_lang('title','templateeditglobal') . '</a>');
        
        $this->addline('<ul class="editlist">');
        foreach ($filelist as $fileid => $file) {
            $this->addline('  <li><a href="?p=template:delete&amp;file=' . $file['name'] . '" class="delete" title="' . get_lang('title','templatedelete') . '">X</a><a href="?p=template:edit&amp;file=' . $file['name'] . '"><span style="color:#000;">http:///</span>' . $file['name'] . '<span style="color:#000;">.php</span></a>');
            $this->addline('      <ul>');
            
            // Display page templates
            foreach ($file['templates'] as $template) {
                $this->addline('          <li><a href="?p=template:page:delete&amp;file=' . $file['name'] . '&amp;page=' . $template['id'] . '" class="delete" title="' . get_lang('title','templatepage:delete') . '">X</a><a href="?p=template:page:edit&amp;file=' . $file['name'] . '&amp;page=' . $template['id'] . '">' . $template['name'] . '</a></li>');
            }
            $this->addline('      </ul>');
            
            $this->addline('  </li>');
        }
        $this->addline('</ul>');
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add File
    ////////////////////////////////////////////////////////////
    function d_add($file=false) {
        $this->newpage('add');
        
        $this->genform($this->fields('add'),plaintext($file),array(
            'img'=>'new_window',
        ));
    }
    function p_add($file=false) {
        if (!$this->readform($file,$this->fields('add'))) return $this->d_add($file);
        
        if (!$file = $this->cpu('file','add',$file)) return $this->d_add($file);
        
        $this->d_edit($file['name']);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit File
    ////////////////////////////////////////////////////////////
    function d_edit($fileid=false,$file=false) {
        if (!$file = plaintext($this->v_fileid($fileid,$file))) return $this->d_main();
        $this->newpage('edit','file='.$fileid);
        
        $this->addline('<h2>'.$file['name'].'.php</h2>');
        
        // Display templates
        if (!empty($file['templates'])) $this->addline('<h3>' . get_lang('title','templatepage:edit') . '</h3>');
        $list = new admin_editlist;
        foreach ($file['templates'] as $page) {
            $list->add(array(
                'id'=>$page['id'],
                'url'=>'?p=template:page:edit&amp;file='.$file['name'].'&amp;page='.$page['id'],
                'title'=>$page['name'],
                'delete'=>'?p=template:page:delete&amp;file='.$file['name'].'&amp;page='.$page['id'],
                'delete_title'=>get_lang('title','templatepage:delete'),
            ));
        }
        $this->addline($list->render('comiclist'));
        $this->addline('<a href="?p=template:page:add&amp;file='.$file['name'].'" class="onenav">' . get_lang('title','templatepage:add') . '</a>');
        $this->addline('<div style="height:10px;"></div>');
        
        // Trigger editor
        if (!empty($file['order'])) {
            $jsarray = array();
            
            // Order Edit
            $html = '<div style="float:right; width:50%; border-left:solid 1px #888; margin:0 0 0 20px; padding:0 10px 10px;">';
            $html .= '<h3>' . get_lang('template','orderedit') . '</h3>';
            $html .= '<p>' . get_lang('template','orderdesc') . '</p>';
            $html .= '<ul id="sortable">';
            foreach ($file['order'] as $k => $template) {
                $template = $file['templates'][$template];
                $html .= '    <li id="'.$k.'"><span class="handle"></span>
                    <span class="data">' . $template['name'] . '</span>
                </li>';
                $jsarray[] = $template['id'];
            }
            $html .= '</ul>';
            $html .= '</div>';
            
            // Trigger Edit
            // Needs to be changed to a wizard/helper sometime
            $html .= '<h3 style="clear:left;">' . get_lang('template','triggeredit') . '</h3>';
            $html .= '<p>' . get_lang('template','triggerdesc') . '</p>';
            foreach ($file['templates'] as $k => $template) {
                $html .= '<fieldset class="text">';
                $html .= '  <label class="inputlabel">'.$template['name'].'</label>';
                $html .= '  <input type="text" name="trigger_'.$k.'" value="'.$template['trigger'].'" />';
                $html .= '</fieldset>';
            }
            
            $html .= '<p style="clear:both;"></p>';
            
            
            $fields = array(
                'triggersort'=>array('type'=>'hidden','value'=>implode(',',$jsarray),'name'=>'input_triggersort','id'=>'triggersort','label'=>false),
                array('type'=>'html','html'=>$html),
            );
            
            $this->genform($fields,array(),array('img'=>'view_tree','title'=>get_lang('template','triggeredit')));
            
            $this->addjavascript('var sort = new Sortables("sortable", {handles:"span.handle",onComplete:function() {
                var order = this.serialize();
                var sortarray = new Array(' . implode(',',$jsarray) . ');
                var result = "";
                for (var x=0;x<order.length;x++) {
                    result = result + sortarray[order[x]] + ",";
                }
                $(\'triggersort\').value = result;
                if (order.length == 1) $(\'triggersort\').value = "1,";
            }});
            
            Nifty("#sortable li","transparent");');
        }
    }
    function p_edit($fileid=false,$file=false) {
        if (!$oldfile = $this->v_fileid($fileid,$file)) return $this->d_main();
        
        if (!$this->readform($file,array('triggersort'=>'text'))) return $this->d_edit($fileid,$file);
        
        // Repack order
        $file['order'] = explode(',',$file['triggersort']);
        if ($p = array_pop($file['order'])) array_push($file['order'],$p);
        
        // Pull template values
        $file['templates'] = array();
        for ($t=1; $t<=count($file['order']); $t++) {
            $file['templates'][$t] = array(
                'name'=>$oldfile['templates'][$t]['name'],
                'trigger'=>$file['trigger_'.$t],
            );
        }
        
        if (!$this->cpu('file','edit',$fileid,$file)) return $this->d_edit($fileid,$file);
        
        return $this->d_edit($fileid);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete File
    ////////////////////////////////////////////////////////////
    function d_delete($fileid=false,$file=false) {
        if (!$file = $this->v_fileid($fileid,$file)) return $this->d_main();
        $this->newpage('delete','file='.$fileid);
        
        if (!empty($file['order'])) {
            $this->addwarning(get_lang('file','pageleft'));
        } else {
            $this->genform($this->fields('delete'),$file,array(
                'img'=>'editdelete',
            ));
        }
    }
    function p_delete($fileid=false,$file=false) {
        if (!$oldfile = $this->v_fileid($fileid,$file)) return $this->d_main();
        
        if (!$this->readform($file,$this->fields('delete'))) return $this->d_delete($fileid,$file);
        
        if (!$this->cpu('file','delete',$fileid)) return $this->d_delete($fileid,$file);
        
        $this->d_main();
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit Global templates
    ////////////////////////////////////////////////////////////
    function f_editglobal() {
        $r = array(
            array('type'=>'html','html'=>'<div id="globaleditor">'),
            'header'=>array('type'=>'textarea','class'=>'resizeTextarea','label'=>false),
            array('type'=>'html','html'=>'<p id="globalseperator">'.get_lang('template','globalseperator').'</p>'),
            'footer'=>array('type'=>'textarea','class'=>'resizeTextarea','label'=>false),
            array('type'=>'html','html'=>'</div>'),
            
            array('type'=>'html','html'=>'<input type="submit" value="'.get_lang('template','savereturn').'" class="savereload" name="save" />'),
            
            array('type'=>'html','html'=>'<h2>'.get_lang('template','globalvars').'</h2>'),
            'varlist'=>'array',
            array('type'=>'html','html'=>'<p>'.get_lang('template','globalvarlist').'</p>'),
            array('type'=>'html','html'=>'<h3>'.get_lang('template','globalvardefault').'</h3>'),
        );
        $globals = $this->cpu('template','get_globals');
        $vars = plaintext(array_keys(array_merge($globals['header']['vars'],$globals['footer']['vars'])));
        foreach ($vars as $var) {
            $r['vars_'.$var] = array('type'=>'text','title'=>'{{'.$var.'}}');
        }
        return $r;
    }
    function d_editglobal($data=false) {
        $this->newpage('editglobal');
        $globals = $this->cpu('template','get_globals');
        if ($data) $globals = $this->cpu('template','format',$globals,$data);
        
        // Unpack templates for form
        $vars = array(
            'header'=>$globals['header']['content'],
            'footer'=>$globals['footer']['content'],
        );
        
        // Unpack vars for form
        $vars['varlist'] = array();
        foreach ($globals['header']['vars'] as $k => $v) {
            $vars['vars_'.$k] = $v;
            $vars['varlist'][] = $k;
        }
        
        $this->genform($this->f_editglobal(),$vars,array(
            'img'=>'view_top_bottom',
        ));
    }
    function p_editglobal($data=false) {
        if (!$this->readform($data,$this->f_editglobal())) return $this->d_editglobal($data);
        
        // Repack templates from form
        $globals = array(
            'header'=>array('content'=>$data['header'],'vars'=>array()),
            'footer'=>array('content'=>$data['footer'],'vars'=>array()),
        );
        
        // Repack vars from form
        foreach ($data['varlist'] as $k) {
            $v = (isset($data['vars_'.$k])?$data['vars_'.$k]:'');
            $globals['header']['vars'][$k] = $v;
            $globals['footer']['vars'][$k] = $v;
        }
        
        if (!$this->cpu('template','edit_globals',$globals)) return $this->d_editglobal($data);
        
        if ( (isset($_POST['save'])) && ($_POST['save'] == get_lang('template','savereturn')) ) {
            return $this->d_editglobal();
        }
        
        $this->d_main();
    }
}

?>