<?php
import('.../lib/admin/lib.php');

class admin_draft extends admin_lib {
    //
    // Note:
    // Draft assimilates the sections of others, so it acts pretty weird
    //
    var $section = 'special';
    var $navigation = array(
        'draft:main'=>1,
        'draft:edit'=>'draft:edit|draft:main',
        'draft:delete'=>'draft:edit',
    );
    var $subsection = 'draft';
    
    
    
    ////////////////////////////////////////////////////////////
    // Grab default form fields
    ////////////////////////////////////////////////////////////
    function fields($section,$switch) {
        import('.../lib/admin/'.filesafe($section).'.php');
        $class = 'admin_'.$section;
        $admin = new $class;
        
        return $admin->fields($switch);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Apply section & page
    ////////////////////////////////////////////////////////////
    function newpage(&$section,$page,$extra=false) {
        setvar($section,'g','section');
        
        switch ($section) {
            case 'comic': case 'news':
                $this->section = $section;
                $extra = 'section='.$section.($extra?'&amp;'.$extra:'');
                break;
            
            default:
                // Bad section
                load_admin('main','main','badsection');
                $this->end();
                break;
        }
        
        parent::newpage('draft:'.$page,$extra);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Get & validate item
    ////////////////////////////////////////////////////////////
    function v_itemid(&$id,$data=false) {
        if (!setvar($id,'g','id')) return $this->warning('noid');
        
        if (!$item = $this->cpu('cron','get',$id)) return $this->error('badid');
        if ($item['function'] != 'add') return $this->error('badid');
        
        if ($data) return $this->cpu('cron','format',$data,$item);
        return $item;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Display main list
    ////////////////////////////////////////////////////////////
    function d_main($section=false) {
        $this->newpage($section,'main');
        
        $items = plaintext($this->cpu('cron','get'));
        $queue = array();
        $drafts = array();
        foreach ($items as $item) if ( ($item['type'] == $section) && ($item['function'] == 'add') ) {
            if ($item['timestamp'] == 9999999999) {
                $drafts[] = $item;
            } else $queue[] = $item;
        }
        
        if (!empty($drafts)) {
            $this->addline('<h2>'.get_lang($section,'drafts').'</h2>');
            $list = new admin_editlist;
            foreach ($drafts as $item) {
                $list->add(array(
                    'url'=>'?p=draft:edit&amp;id='.$item['id'],
                    'title'=>(( (isset($item['data']['title'])) && ($item['data']['title']) )?$item['data']['title']:get_lang($this->section,'untitled'))
                        .' - '
                        .get_lang($section,'draft'),
                    'delete'=>'?p=draft:delete&amp;id='.$item['id'],
                    'delete_title'=>get_lang('title',$section.'draft:delete'),
                ));
            }
            // Display list
            $this->addline($list->render());
        }
        
        if (!empty($queue)) {
            $this->addline('<h2>'.get_lang($section,'queued').'</h2>');
            $list = new admin_editlist;
            foreach ($queue as $item) {
                $list->add(array(
                    'url'=>'?p=draft:edit&amp;id='.$item['id'],
                    'title'=>(( (isset($item['data']['title'])) && ($item['data']['title']) )?$item['data']['title']:get_lang($this->section,'untitled'))
                        .' - '
                        .date(get_lang('date','short_both'),$item['timestamp']),
                    'delete'=>'?p=draft:delete&amp;id='.$item['id'],
                    'delete_title'=>get_lang('title',$section.'draft:delete'),
                ));
            }
            // Display list
            $this->addline($list->render());
        }
    }
    function d_comic() {$this->d_main('comic');}
    function d_news() {$this->d_main('news');}
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit job
    ////////////////////////////////////////////////////////////
    function d_edit($id=false,$item=false) {
        if (!$item = $this->v_itemid($id,$item)) return $this->d_main();
        $this->newpage($item['type'],'edit','id='.$item['id']);
        
        $fields = $this->fields($item['type'],'editdata');
        
        if ($item['type']=='comic') {
            $item['files']['comicimg']['src'] = '?type=img&id='.substr($item['files']['comicimg']['tmp_name'],7);
            array_unshift($fields,array('type'=>'html','html'=>'<div style="clear:left;">'.$this->showcomic($item['files']['comicimg']).'</div>'));
        }
        
        $extra = array('img'=>'kword');
        if (!empty($_FILES)) {
            $extra['title'] = get_lang('title',$item['type'].'add');
            if ($item['data']['timestamp'] == 9999999999) $item['data']['timestamp'] = date('U');
        }
        
        $this->genform($fields,$item['data'],$extra);
    }
    function p_edit($id=false,$item=false) {
        if (!$olditem = $this->v_itemid($id)) return $this->d_main();
        
        if (!$this->readform($item,$this->fields($olditem['type'],'editdata'))) return $this->d_edit($id,$item);
        
        $item = array_merge($olditem['data'],$item);
        
        if (!get_config('allowutf8')) {
        	if (isset($item['blurb'])) $item['blurb'] = scrub_utf8($item['blurb']);
        	if (isset($item['title'])) $item['title'] = scrub_utf8($item['title']);
        	if (isset($item['tagline'])) $item['tagline'] = scrub_utf8 ($item['tagline']);
        }
        
        if (!$new = $this->cpu($olditem['type'],'add',$item,$olditem['files'])) return $this->d_edit($id,$item);
        
        $this->silence();
        $this->cpu('cron','delete',$id);
        $this->speak();
        
        
        
        if ($item['timestamp'] == 9999999999)  return $this->d_main($olditem['type']);
        
        if ($item['timestamp'] <= date('U')) {
            if ($olditem['type']=='comic') $this->reload_comicimg($new);
            unset($_POST['save']);
            $_GET[$olditem['type']] = $new['id'];
            load_admin($olditem['type'],'edit');
        } else $this->d_main($olditem['type']);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete job
    ////////////////////////////////////////////////////////////
    function d_delete($id=false,$item=false) {
        if (!$item = $this->v_itemid($id,$item)) return $this->d_main();
        $this->newpage($item['type'],'delete','id='.$item['id']);
        
        $fields = array();
        
        if ($item['type']=='comic') {
            $item['files']['comicimg']['src'] = '?type=img&id='.substr($item['files']['comicimg']['tmp_name'],7);
            array_unshift($fields,array('type'=>'html','html'=>'<div style="clear:left; text-align:center;">'.$this->showcomic($item['files']['comicimg']).'</div>'));
        }
        
        $this->genform($fields,$item,array(
            'img'=>'editdelete',
        ));
    }
    function p_delete($id=false,$item=false) {
        if (!$olditem = $this->v_itemid($id)) return $this->d_main();
        
        if (!$this->readform($item,array())) return $this->d_delete($id,$item);
        
        $this->silence();
        if (!$this->cpu('cron','delete',$id)) return $this->d_delete($id,$item);
        $this->speak();
        $this->addgoodmessage(get_lang($olditem['type'],'delete_good'));
        
        $this->d_main($olditem['type']);
    }
    
}
?>
