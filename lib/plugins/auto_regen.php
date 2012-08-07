<?php
//
//  Auto Regen
//  -------------------
//  - Author: Steve H <http://comiccms.com/>
//  - License: GPL 3 <http://www.gnu.org/copyleft/gpl.html>
//  - Created: May 2008
//  - Updated: July 2009
//
//  Designed for ComicCMS 0.1.9b <http://comiccms.com/>
//
class pl_auto_regen extends ComicCMS {
    var $id = 'auto_regen';
    var $version = '1';
    var $forversion = array('0.1.9alpha6','0.1.9alpha7','0.1.9b','0.2RC1','0.2');
    var $events = array(
        'after_comic_add','after_comic_edit','after_comic_delete',
        'after_news_add','after_news_edit','after_news_delete',
        'after_page_edit','after_page_delete','after_template_edit_globals',
        'after_plugin_edit','after_plugin_delete',
        'after_user_edit','after_user_delete',
    );
    
    var $lang = array(
        '_lang'=>'en',
        'en'=>array(
            '_title'=>'Auto Regen',
            '_desc'=>'Automatically Regenerate Cache Files',
            '_author'=>'Steve H',
            
            'main'=>'Auto Regen is a core plugin of ComicCMS. It works with your templates and admin panel to ensure the public site is kept up to date. It\'s the key to the cache system that makes your public site work so great under high loads.',
            'delete'=>'Auto Regen is a core plugin of ComicCMS, you may not remove this plugin',
        )
    );
    
    function d_use() {
        $GLOBALS['adminpage']->addline('<p>'.$this->get_lang('main').'</p>');
    }
    function d_delete() {
        $GLOBALS['adminpage']->adderror($this->get_lang('delete'));
    }
    function p_delete() {
        $GLOBALS['adminpage']->adderror($this->get_lang('delete'));
        return false;
    }
    
    
    
    function rebuild($search=array()) {
        $cachecpu = load_cpu('cache');
        $cachecpu->get_delete($search);
    }
    
    
    
    function item_add($type,$item) {
        $this->rebuild(array($type=>'-0'));
    }
    function item_edit($type,$item) {
        if ($item['islast']) {
            $this->rebuild(array($type=>'-0'));
        } elseif ($item['isfirst']) {
            $this->rebuild(array($type=>'+0'));
        } else $this->rebuild(array($type=>$item['id']));
    }
    
    function after_comic_add($comic) {$this->item_add('comic',$comic);}
    function after_comic_edit($comic) {$this->item_edit('comic',$comic);}
    function after_comic_delete($comic) {$this->item_edit('comic',$comic);}
    
    function after_news_add($news) {$this->item_add('news',$news);}
    function after_news_edit($news) {$this->item_edit('news',$news);}
    function after_news_delete($news) {$this->item_edit('news',$news);}
    
    
    
    function page_edit($fileid,$page) {
        $this->rebuild(array(
            'id'=>'tpl_'.$fileid,
            'subid'=>$page['id'],
        ));
    }
    function after_template_edit($fileid,$page) {$this->page_edit($fileid,$page);}
    function after_template_delete($fileid,$page) {$this->page_edit($fileid,$page);}
    
    function after_template_edit_globals() {$this->rebuild('globals');}
    
    
    
    function plugin_edit($plugin) {
        $this->rebuild(array('id'=>'pl_'.$plugin['name']));
    }
    function after_plugin_edit($plugin) {$this->plugin_edit($plugin);}
    function after_plugin_delete($plugin) {$this->plugin_edit($plugin);}
    
    
    function after_user_edit($user) {$this->rebuild(false);}
    function after_user_delete($user) {$this->rebuild(false);}
}
?>