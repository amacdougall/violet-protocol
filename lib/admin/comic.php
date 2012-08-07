<?php
import('.../lib/admin/lib.php');

class admin_comic extends admin_lib {
    var $section = 'comic';
    var $navigation = array(
        'add'=>1,
        'draft:comic'=>1,
        //'tags'=>1,
        //'addcollection'=>'tags',
        //'editcollection'=>'editcollection|tags',
        //'deletecollection'=>'editcollection|tags',
        'edit'=>false,
        'editimg'=>'edit',
        'editdata'=>'edit',
        'delete'=>'edit',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch,$comicid=false) {
        switch ($switch) {
            case 'addimg':
                return array(
                    'comicimg'=>array('type'=>'file','label'=>false),
                );
                break;
                
            case 'editdata':
                $fields = array(
                    'title'=>'text',
                    'tagline'=>'text',
                    'blurb'=>array('type'=>'textarea','class'=>'resizeTextarea'),
                    'timestamp'=>new timestamp_input,
                    'tag'=>'array',
                );
                
                if ($comicid) {
                    // Find current, prev and next timestamps and apply limits
                    $comics = $this->cpu('comic','get','batch',$comicid,'+1','-1');
                    $fields['timestamp']->upperlimit(date('U'));
                    foreach ($comics as $comic) {
                        if ($comic['id'] < $comicid) $fields['timestamp']->lowerlimit($comic['timestamp']);
                        if ($comic['id'] > $comicid) $fields['timestamp']->upperlimit($comic['timestamp']);
                    }
                } else {
                    // Find last timestamp and apply limit
                    if ($last = $this->cpu('comic','get','-0')) {
                        $fields['timestamp']->lowerlimit($last['timestamp']);
                    }
                    $fields['timestamp']->allowdraft();
                }
                
                return $fields;
                break;
            
            case 'editimg':
                return array(
                    'comicimg'=>'file',
                );
                break;
            
            case 'delete':
                return array(
                    
                );
                break;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Get & validate comics
    ////////////////////////////////////////////////////////////
    function v_comicid(&$comicid,$data=false,$batch1=false,$batch2=false,$batch3=false) {
        if (!setvar($comicid,'g','comic')) {
            $this->warning('comicnoid');
            return false;
        }
        
        if (!$comics = $this->v_batch('comic',$comicid,$batch1,$batch2,$batch3)) {
            $this->error('comicbadid');
            return false;
        }
        
        if ($data) return $this->cpu('comic','format',$data,$comics);
        return $comics;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Main page
    ////////////////////////////////////////////////////////////
    function d_main($offset=false) {
        $this->newpage('main');
        
        if (!setvar($offset,'g','offset')) $offset = 0;
        if (!is_numeric($offset)) $offset = 0;
        $offset--;
        if ($offset<0) $offset=0;
        
        
        $textmode = false;
        if (setvar($textmode,'g','view')=='list') {
            $textmode = true;
            setcookie("ccms_view", 'list',time()+9999999,'/');
        } elseif ($textmode) {
            $textmode = false;
            setcookie("ccms_view", 'img',time()-3600,'/');
        } elseif ( (isset($_COOKIE['ccms_view'])) && ($_COOKIE['ccms_view']=='list') ) {
            $textmode = true;
        } else $textmode = false;
        
        
        
        // Thumbnail list
        if ( (function_exists('gd_info')) && (!$textmode) ) {
            if ($comics = plaintext($this->cpu('comic','get','batch','-'.$offset,-30))) {
                $this->addline('<h2>Click a comic to edit</h2>');
                $this->addline('<div style="margin:10px 0; height:230px; width:100%; overflow:auto; white-space:nowrap;">');
                foreach ($comics as $comic) {
                    if (!is_readable(makepath('.../img/thumb/'.$comic['id'].'.jpg'))) {
                        $this->cpu('comic','admin_thumbnail',$comic['id'] , $comic['id'].'.'.$comic['ext']);
                    }
                    $this->addline('<a href="?p=comic:edit&amp;comic='.$comic['id'].'" title="'.$comic['title'].'"><img src="img/thumb/'.$comic['id'].'.jpg" alt="'.($comic['title']?$comic['title']:get_lang('comic','untitled')).'" /></a>');
                }
                $this->addline('</div>');
                
                // Display links
                $prevurl = ($offset>0?'?p=comic&amp;offset=' . (($offset-30)>0?($offset-30):0):false);
                $nexturl = ($comic['isfirst']?false:'?p=comic&amp;offset=' . ($offset+30));
            
                $this->addline(($prevurl?'<a href="' . $prevurl . '" class="twonav_1">' . get_lang('comic','prevoffset',30) . '</a>':'<span class="twonav_1">' . get_lang('comic','prevoffset',0) . '</span>'));
                $this->addline(($nexturl?'<a href="' . $nexturl . '" class="twonav_2">' . get_lang('comic','nextoffset',30) . '</a>':'<span class="twonav_2">' . get_lang('comic','nextoffset',0) . '</span>'));
                
                $this->addline('<div style="text-align:center; font-size:12px;"><a href="?p=comic&amp;offset='.$offset.'&amp;view=list">'.get_lang('comic','switchlist').'</a></div>');
            } else {
                $this->addline('<p style="display:inline; margin:0 0 0 70px; padding:5px 10px; color:#228; background-color:#dec;"><img src="img/crystal/14_layer_raiselayer.png" alt="^" /> '.get_lang('comic','addflag').'</p>');
                $this->addline('<p style="width:600px; margin:20px auto; padding:10px; border:solid 1px #888;">'.get_lang('comic','nocomics').'</p>');
            }
        
        // Text list
        } else {
            // Load comics
            if ($comics = plaintext($this->cpu('comic','get','batch','-'.$offset,-15))) {
                if (isset($comics[$offset])) unset($comics[$offset]);
                
                // Display comics
                $list = new admin_editlist;
                foreach ($comics as $comic) {
                    $list->add(array(
                        'id'=>$comic['id'],
                        'url'=>'?p=comic:edit&amp;comic='.$comic['id'],
                        'title'=>($comic['title']?$comic['title']:get_lang('comic','untitled')),
                        'delete'=>'?p=comic:delete&amp;comic='.$comic['id'],
                        'delete_title'=>get_lang('title','comicdelete'),
                    ));
                }
                $this->addline($list->render('comiclist'));
                
                // Display links
                $prevurl = ($offset>0?'?p=comic&amp;offset=' . (($offset-15)>0?($offset-15):0):false);
                $nexturl = ($comic['isfirst']?false:'?p=comic&amp;offset=' . ($offset+15));
            
                $this->addline(($prevurl?'<a href="' . $prevurl . '" class="twonav_1">' . get_lang('comic','prevoffset',15) . '</a>':'<span class="twonav_1">' . get_lang('comic','prevoffset',0) . '</span>'));
                $this->addline(($nexturl?'<a href="' . $nexturl . '" class="twonav_2">' . get_lang('comic','nextoffset',15) . '</a>':'<span class="twonav_2">' . get_lang('comic','nextoffset',0) . '</span>'));
                
                if (function_exists('gd_info')) $this->addline('<div style="text-align:center; font-size:12px;"><a href="?p=comic&amp;offset='.$offset.'&amp;view=img">'.get_lang('comic','switchthumb').'</a></div>');
            } else {
                $this->addline('<p style="display:inline; margin:0 0 0 70px; padding:5px 10px; color:#228; background-color:#dec;"><img src="img/crystal/14_layer_raiselayer.png" alt="^" /> '.get_lang('comic','addflag').'</p>');
                $this->addline('<p style="width:600px; margin:20px auto; padding:10px; border:solid 1px #888;">'.get_lang('comic','nocomics').'</p>');
            }
        }
    }
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit screen
    ////////////////////////////////////////////////////////////
    function d_edit($comicid=false,$renue=true) {
        if (!$comics = plaintext($this->v_comicid($comicid,false,1,-1))) return $this->d_main();
        $this->newpage('edit','comic=' . $comicid);
        
        // Pluck out comics
        $comic = $comics[$comicid];
        if (!$comic['isfirst']) $prev = array_shift($comics);
        if (!$comic['islast']) $next = array_pop($comics);
        
        
        $this->addline((isset($next)?'<a href="?p=comic:edit&comic=' . $next['id'] . '" class="twonav_1">' . get_lang('comic','nextcomic') . '</a>':'<span class="twonav_1">' . get_lang('comic','nextcomic') . '</span>'));
        $this->addline((isset($prev)?'<a href="?p=comic:edit&comic=' . $prev['id'] . '" class="twonav_2">' . get_lang('comic','prevcomic') . '</a>':'<span class="twonav_2">' . get_lang('comic','prevcomic') . '</span>'));
        
        $this->addline('<h3>' . '#' . $comic['id'] . ' :: ' . ($comic['title']?$comic['title']:get_lang('comic','untitled')) . '</h3>');
        
        $this->addline('<div style="text-align:center; margin-bottom:10px;">');
        $bcomic = $comic; $bcomic['src'] .= '?r='.time();
        $this->addline('  <a href="' . $comic['src'] . '" target="_blank">'.$this->showcomic($bcomic).'</a>');
        $this->addline('</div>');
    }
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Add Comic
    ////////////////////////////////////////////////////////////
    function d_add($comic=false) {
        $this->newpage('add');
        
        if (!isset($comic['uploaded'])) {
            $this->genform($this->fields('addimg'),$comic,array(
                'enctype'=>'multipart/form-data',
                'img'=>'kpaint',
                'html'=>'<h3 style="text-align:left;">' . get_lang('comic','comicimage') . '</h3>',
                'submit'=>get_lang('comic','uploadcomic'),
            ));
        } else {
            $this->addline('<p class="notice">'.get_lang('comic','imgadd').'</p>');
            
            $_GET['section'] = 'comic';
            $_GET['id'] = $comic['cron']['id'];
            unset($_POST['save']);
            load_admin('comic','draft:edit');
        }
    }
    function p_add($comic=false,$files=false) {
        $files = $this->readform($comic,$this->fields('addimg'),$files);
        if ($files===false) return $this->d_add();
        
        
        $comic = array('timestamp'=>0,'uploaded'=>false);
        
        $comic = $this->cpu('comic','add',$comic,$files['comicimg']);
        
        $this->d_add($comic);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit Comic Data
    ////////////////////////////////////////////////////////////
    function d_editdata($comicid=false,$data=false) {
        if (!$comic = $this->v_comicid($comicid,$data)) return $this->d_main();
        $this->newpage('editdata','comic=' . $comicid);
        
        $this->genform($this->fields('editdata',$comicid),$comic,array(
            'img'=>'kword',
        ));
    }
    function p_editdata($comicid=false,$comic=false) {
        if (!$oldcomic = $this->v_comicid($comicid)) return $this->d_main();
        
        if (!$this->readform($comic,$this->fields('editdata',$comicid))) return $this->d_editdata($comicid,$comic);
        
        if (!get_config('allowutf8')) {
            $comic['blurb'] = scrub_utf8($comic['blurb']);
            $comic['title'] = scrub_utf8($comic['title']);
            $comic['tagline'] = scrub_utf8 ($comic['tagline']);
        }
        
        if (!$this->cpu('comic','edit',$comicid,$comic)) return $this->d_editdata($comicid,$comic);
        
        $this->d_edit($comicid);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit Comic Image
    ////////////////////////////////////////////////////////////
    function d_editimg($comicid=false) {
        if (!$comic = $this->v_comicid($comicid)) return $this->d_main();
        $this->newpage('editimg','comic=' . $comicid);
        
        $fields = $this->fields('editimg');
        if (function_exists('gd_info')) $fields[] = array('type'=>'html','html'=>'<img src="img/thumb/'.$comic['id'].'.jpg" alt="" />');
        
        $this->genform($fields,$comic,array(
            'enctype'=>'multipart/form-data',
            'img'=>'kpaint',
        ));
    }
    function p_editimg($comicid=false,$comic=false,$files=false) {
        if (!$oldcomic = $this->v_comicid($comicid)) return $this->d_main();
        
        $files = $this->readform($comic,$this->fields('editimg'),$files);
        if ($files===false) return $this->d_editimg($comicid);
        
        if (!$comic = $this->cpu('comic','edit',$comicid,$oldcomic,$files['comicimg'])) return $this->d_editimg($comicid);
        
        $this->reload_comicimg($comic);
        
        $this->d_edit($comicid,true);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete Comic
    ////////////////////////////////////////////////////////////
    function d_delete($comicid=false,$data=false) {
        if (!$comic = $this->v_comicid($comicid)) return $this->d_main();
        $this->newpage('delete','comic=' . $comicid);
        
        $this->genform($this->fields('delete'),$comic,array(
            'img'=>'editdelete',
            'html'=>'<div style="clear:left; text-align:center;">'.$this->showcomic($comic).'</div>',
        ));
    }
    function p_delete($comicid=false,$comic=false) {
        if (!$oldcomic = $this->v_comicid($comicid)) return $this->d_main();
        
        if (!$this->readform($comic,$this->fields('delete'))) return $this->d_delete($comicid,$comic);
        
        if (!$this->cpu('comic','delete',$comicid)) return $this->d_delete($comicid,$comic);
        
        $this->d_main();
    }
}

?>
