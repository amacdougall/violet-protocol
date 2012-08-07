<?php
import('.../lib/admin/lib.php');

class admin_news extends admin_lib {
    var $section = 'news';
    var $navigation = array(
        'add'=>1,
        'draft:news'=>1,
        'edit'=>false,
        'editdata'=>'edit',
        'delete'=>'edit|1',
        'editcomment'=>'editcomment|edit',
        'deletecomment'=>'editcomment|edit',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch,$newsid=false) {
        switch($switch) {
            case 'add': case 'editdata':
                $fields = array(
                    'title'=>'text',
                    'post'=>array('type'=>'textarea','class'=>'resizeTextarea'),
                    'timestamp'=>new timestamp_input,
                );
                
                if ($newsid) {
                    // Find current, prev and next timestamps and apply limits
                    $news = $this->cpu('news','get','batch',$newsid,'+1','-1');
                    $fields['timestamp']->upperlimit(date('U'));
                    foreach ($news as $post) {
                        if ($post['id'] < $newsid) $fields['timestamp']->lowerlimit($post['timestamp']);
                        if ($post['id'] > $newsid) $fields['timestamp']->upperlimit($post['timestamp']);
                    }
                } else {
                    // Find last timestamp and apply limit
                    if ($last = $this->cpu('news','get','-0')) {
                        $fields['timestamp']->lowerlimit($last['timestamp']);
                    }
                    if ($switch == 'add') $fields['timestamp']->setvalue(date('U'));
                    $fields['timestamp']->allowdraft();
                }
                
                return $fields;
                break;
            
            case 'delete':
                return array(
                
                );
                break;
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Get & validate news posts
    ////////////////////////////////////////////////////////////
    function v_newsid(&$newsid,$data=false,$batch1=false,$batch2=false,$batch3=false) {
        if (!setvar($newsid,'g','news')) {
            $this->warning('newsnoid');
            return false;
        }
        
        if (!$news = $this->v_batch('news',$newsid,$batch1,$batch2,$batch3)) {
            $this->error('newsbadid');
            return false;
        }
        
        if ($data) return $this->cpu('news','format',$data,$news);
        return $news;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Main page
    ////////////////////////////////////////////////////////////
    function d_main($offset=false) {
        $this->newpage('main');
        
        if (!setvar($offset,'g','offset')) $offset=0;
        if (!is_numeric($offset)) $offset = 0;
        $offset--;
        if ($offset<0) $offset=0;
        
        // Load news
        if ($newsarr = plaintext($this->cpu('news','get','batch','-'.$offset,-15))) {
            // Display news
            $list = new admin_editlist;
            foreach ($newsarr as $news) {
                $list->add(array(
                    'id'=>$news['id'],
                    'url'=>'?p=news:edit&amp;news='.$news['id'],
                    'title'=>($news['title']?$news['title']:get_lang('news','untitled')),
                    'delete'=>'?p=news:delete&amp;news='.$news['id'],
                    'delete_title'=>get_lang('title','newsdelete'),
                ));
            }
            $this->addline($list->render('newslist'));
            
            // Display links
            $prevurl = ($offset>0?'?p=news&amp;offset=' . (($offset-15)>0?($offset-15):0):false);
            $nexturl = ($news['isfirst']?false:'?p=news&amp;offset=' . ($offset+15));
        
            $this->addline(($prevurl?'<a href="' . $prevurl . '" class="twonav_1">' . get_lang('news','prevoffset',15) . '</a>':'<span class="twonav_1">' . get_lang('news','prevoffset',0) . '</span>'));
            $this->addline(($nexturl?'<a href="' . $nexturl . '" class="twonav_2">' . get_lang('news','nextoffset',15) . '</a>':'<span class="twonav_2">' . get_lang('news','nextoffset',0) . '</span>'));
        } else {
            $this->addline('<p style="display:inline; margin:0 0 0 70px; padding:5px 10px; color:#228; background-color:#dec;"><img src="img/crystal/14_layer_raiselayer.png" alt="^" /> '.get_lang('news','addflag').'</p>');
            $this->addline('<p style="width:600px; margin:20px auto; padding:10px; border:solid 1px #888;">'.get_lang('news','nonews').'</p>');
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit screen
    ////////////////////////////////////////////////////////////
    function d_edit($newsid=false) {
        if (!$news = $this->v_newsid($newsid,false,1,-1)) return $this->d_main();
        $this->newpage('edit','news=' . $newsid);
        
        // Pluck out posts
        $post = $news[$newsid];
        if (!$post['isfirst']) $prev = array_shift($news);
        if (!$post['islast']) $next = array_pop($news);
        
        $this->addline((isset($next)?'<a href="?p=news:edit&news=' . $next['id'] . '" class="twonav_1">' . get_lang('news','nextnews') . '</a>':'<span class="twonav_1">' . get_lang('news','nextnews') . '</span>'));
        $this->addline((isset($prev)?'<a href="?p=news:edit&news=' . $prev['id'] . '" class="twonav_2">' . get_lang('news','prevnews') . '</a>':'<span class="twonav_2">' . get_lang('news','prevnews') . '</span>'));
        
        $this->addline('<h3>'.($post['title']?plaintext($post['title']):get_lang('news','untitled')).'</h3>');
        $nbbc = load_class('nbbc');
        $this->addline('<div style="margin:10px 10%; padding:10px; border:solid 2px #ccc; border-bottom:none 0px; background-color:#fff;">'.$nbbc->richtext($post['post']).'</div>');
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Add news
    ////////////////////////////////////////////////////////////
    function d_add($news=array()) {
        $this->newpage('add');
        
        $this->genform($this->fields('add'),plaintext($news),array(
            'img'=>'kate',
        ));
    }
    function p_add($news=array()) {
        if (!$this->readform($news,$this->fields('add'))) return $this->d_add($news);
        
        if (!$new = $this->cpu('news','add',$news)) return $this->d_add($news);
        
        if ($new['id']=='cron') {
            unset($_POST['save']);
            load_admin('news','draft:news');
            return true;
        }
        $this->d_edit($new['id']);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit news data
    ////////////////////////////////////////////////////////////
    function d_editdata($newsid=false,$data=false) {
        if (!$news = $this->v_newsid($newsid,$data)) return $this->d_main();
        $this->newpage('editdata','news=' . $newsid);
        
        $this->genform($this->fields('editdata',$newsid),$news,array(
            'img'=>'kate',
        ));
    }
    function p_editdata($newsid=false,$news=array()) {
        if (!$oldnews = $this->v_newsid($newsid)) return $this->d_main();
        
        if (!$this->readform($news,$this->fields('editdata',$newsid))) return $this->d_editdata($newsid,$news);
        
        if (!$this->cpu('news','edit',$newsid,$news)) return $this->d_editdata($newsid,$news);
        
        $this->d_edit($newsid);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete news post
    ////////////////////////////////////////////////////////////
    function d_delete($newsid=false,$data=false) {
        if (!$news = $this->v_newsid($newsid,$data)) return $this->d_main();
        $this->newpage('delete','news=' . $newsid);
        
        $this->genform($this->fields('delete'),$news,array(
            'img'=>'editdelete',
        ));
    }
    function p_delete($newsid=false,$news=array()) {
        if (!$oldnews = $this->v_newsid($newsid)) return $this->d_main();
        
        if (!$this->readform($news,$this->fields('delete'))) return $this->d_delete($newsid,$news);
        
        if (!$this->cpu('news','delete',$newsid)) return $this->d_delete($newsid,$news);
        
        $this->d_main();
    }
}

?>