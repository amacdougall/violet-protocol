<?php
class ComicCMS {
    function load($plugin=false) {
        if (!is_array($plugin)) {
            $plugincpu = load_cpu('plugin');
            $plugin = $plugincpu->get($this->id);
            if (!$plugin) return false;
        }
        
        if (@isset($this->settings)) {
            $this->settings = $plugin['settings'];
            if (isset($this->settings['template'])) {
                $this->settings['template'] = templatetext('unstore',$this->settings['template']);
            }
        }
    }
    
    function save() {
        if (@isset($this->settings)) {
            $tmp = $this->settings;
            if (isset($tmp['template'])) {
                $tmp['template'] = templatetext('store',$tmp['template']);
            }
            $plugincpu = load_cpu('plugin');
            return $plugincpu->edit($this->id,array('settings'=>$tmp));
        }
    }
    
    function delete($full=false) {
        $plugincpu = load_cpu('plugin');
        $plugincpu->delete($this->id,$full);
        
        $file = load_file('.../lib/plugins/'.filesafe($this->id).'.php');
        return $file->delete();
    }
    
    function settingfile($file,$check=false) {
        return load_file('.../storage/plugins/'.$this->id.'/'.$file,$check);
    }
    
    function lang($lang,$extra=false) {
        if (@isset($this->lang)) {
            if (isset($this->lang['_lang'])) {
                $l = $this->lang[$this->lang['_lang']];
                $loc = get_config('lang_locale');
                if ( ($loc!=$this->lang['_lang']) && (isset($this->lang[$loc])) ) $l = array_merge($l,$this->lang[$loc]);
                $this->lang = $l;
            }
            if (!isset($this->lang[$lang])) return false;
            if ($extra!==false) return str_replace('{{info}}',plaintext($extra),$this->lang[$lang]);
            return $this->lang[$lang];
        } else return false;
    }
    
    function setting_dir() {
        return '.../storage/plugins/'.$this->id.'/';
    }
}
?>