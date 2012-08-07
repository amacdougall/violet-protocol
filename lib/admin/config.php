<?php
import('.../lib/admin/lib.php');

class admin_config extends admin_lib {
    var $section = 'config';
    var $navigation = array(
        'user:main'=>false,
        'usergroup:main'=>false,
        
        
        'cache'=>false,
        
        'setting'=>1,
        'update'=>1,
        'uninstall'=>false,
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Config contents
    ////////////////////////////////////////////////////////////
    function d_main() {
        $this->newpage('main');
        
        foreach ($this->navigation as $page => $loc) {
            if ( (!$loc) || ($loc === $page) || ($loc === 1) ) {
                $this->addline('<a href="?p=config:' . $page . '" class="onenav">' . get_lang('title','config' . $page) . '</a>');
            }
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Manage Cache
    ////////////////////////////////////////////////////////////
    function f_cache() {
        return array(
            'pagecache'=>'checkbox',
            'plugincache'=>'checkbox',
        );
    }
    function d_cache() {
        $this->newpage('cache');
        
        $this->genform($this->f_cache(),array('pagecache'=>true,'plugincache'=>true),array(
            'img'=>'editdelete',
        ));
    }
    function p_cache($data=false) {
        if (!$this->readform($data,$this->f_cache())) return $this->d_cache();
        
        if (!$this->cpu('cache','clear',$data)) return $this->d_cache();
        
        $this->d_main();
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Edit settings
    ////////////////////////////////////////////////////////////
    function f_setting() {
        $settings = $this->cpu('setting','get');
        
        $fields = array();
        $values = array();
        foreach ($settings as $id => $setting) {
            $values[$id] = $setting['value'];
            $label = (get_lang('settings',$setting['name'],false,false,true)?get_lang('settings',$setting['name']):$setting['name']);
            switch($setting['type']) {
                case 'string':
                    $fields[$id] = array('type'=>'text','title'=>$label);
                    break;
                case 'bool':
                    $fields[$id] = array('type'=>'checkbox','title'=>$label);
                    break;
                case 'array':
                    $fields[$id] = array('type'=>'array','title'=>$label);
                    break;
                case 'hidden':break;
                default:
                    $this->adddebug('Unsupported setting type');
                    break;
            }
        }
        
        return array($fields,$values);
    }
    function d_setting($data=false) {
        $this->newpage('setting');
        
        list($fields,$values) = $this->f_setting();
        
        if ($data) $values = $data;
        
        $this->genform($fields,$values,array(
            'img'=>'kbackgammon_engine',
        ));
    }
    function p_setting($data=false) {
        list($fields,$values) = $this->f_setting();
        
        if (!$this->readform($data,$fields)) return $this->d_setting();
                
        if (!$this->cpu('setting','editall',$data)) return $this->d_setting();
        
        $this->d_main();
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Update ComicCMS
    ////////////////////////////////////////////////////////////
    function f_update($json) {
        $json = plaintext($json);
        
        $required = array();
        $optional = array();
        foreach ($json['updates'] as $update) {
            if ($update['type']=='r') $required[] = $update;
            if ($update['type']=='o') $optional[] = $update;
        }
        
        $fields = array();
        $values = array();
        
        $fields[] = array('type'=>'html','html'=>'<div style="width:500px; padding:0 10px 20px; margin:0 auto; border:dashed 1px #888; border-top:none 0px; border-bottom:none 0px;">');
        if (!empty($required)) {
            $fields[] = array('type'=>'html','html'=>'<h2>'.get_lang('config','requiredupdates').'</h2>');
            foreach ($required as $update) {
                $fields[] = array('type'=>'html','html'=>'<h3 style="text-align:left;">'.$update['name'].'</h3><p>'.$update['description'].'</p></li>');
            }
        }
        
        if (!empty($optional)) {
            $fields[] = array('type'=>'html','html'=>'<h2>'.get_lang('config','optionalupdates').'</h2>');
            foreach ($optional as $update) {
                $fields[$update['aflag']] = array('type'=>'checkbox','title'=>$update['name']);
                $fields[] = array('type'=>'html','html'=>'<p style="margin-top:0;">'.$update['description'].'</p>');
                $values[$update['aflag']] = (in_array($update['iflag'],get_config('upgradeflags'))?0:$update['default']);
            }
        }
        $fields[] = array('type'=>'html','html'=>'</div>');
        
        return array($fields,$values);
    }
    function d_update() {
        $this->newpage('update');
        
        // Download update list
        $download = load_class('download');
        $json = $download->download(constant('ComicCMS_HQ').'updates.php?type=list&version='.urlencode(constant('ComicCMS')).'&upgradeflags='.urlencode(@implode(',',get_config('upgradeflags'))));
        if (!$json) return $this->error('noupdatedownload');
        if (!$json = json_decode($json,true)) return $this->error('badupdatedownload');
        
        // Save
        $file = load_file('tmp:///updates');
        $file->write(json_encode($json));
        
        // Return on no updates
        if (!$json['update']) {
            $this->addline('<p class="notice">'.get_lang('config','noupdates').'</p>');
            return true;
        }
        
        if ($json['version']) {
            $this->genform(array(
                array('type'=>'html','html'=>'<h2>'.get_lang('config','updateversion').'</h2>'),
            ),array(),array('img'=>'web','title'=>get_lang('config','applyupdates')));
        } else {
            // Display update form
            list($fields,$values) = $this->f_update($json);
            $this->genform($fields,$values,array('img'=>'web','title'=>get_lang('config','applyupdates')));
        }
    }
    function p_update($data=false,$updatelist=false) {
        // Load update list
        $file = load_file('tmp:///updates');
        $json = json_decode($file->read('string'),true);
        
        $this->newpage('update');
        
        if ($json['version']) {
            $download = load_class('download');
            if (!$download->download($json['url'],'.../download.php')) return $this->error('updatedownloaderror');
            
            $this->addjavascript('document.location = "../download.php";');
            return true;
        }
        
        $upgradeflags = get_config('upgradeflags');
        
        if (!setvar($updatelist,'p','updatelist')) {
            list($fields,$values) = $this->f_update($json);
            
            if (!$this->readform($data,$fields)) return $this->d_update();
            
            $updatelist = array();
            foreach ($json['updates'] as $id => $update) {
                if ( ($update['type']=='r') || ($data[$update['aflag']]) ) array_push($updatelist,'d'.$id,'i'.$id);
                if ( ($update['type']=='o') && (!$data[$update['aflag']]) && (!in_array($update['iflag'],$upgradeflags)) ) $upgradeflags[] = $update['iflag'];
            }
            $message = 'updatebegin';
        } else {
            $updatelist = explode(',',$updatelist);
            $updatestring = array_shift($updatelist);
            $id = substr($updatestring,1);
            
            $update = $json['updates'][$id];
            
            switch (substr($updatestring,0,1)) {
                case 'd':
                    $download = load_class('download');
                    if (!$download->download($update['url'],'.../storage/tmp/update'.$id.'.tar')) return $this->error('updatedownloaderror');
                    $message = 'updatedownloaded';
                    break;
                case 'i':
                    $tar = load_class('tar');
                    $tar->load(makepath('.../storage/tmp/update'.$id.'.tar'));
                    
                    // Folders
                    import('.../lib/filesystem.php');
                    $first = true;
                    while ( ($first) || ($tar->next()) ) {
                        $first = false;
                        $file = $tar->data();
                        if ($file['type']==5) {
                            if (substr($file['name'],0,1)=='/') $file['name'] = substr($file['name'],1);
                            if (substr($file['name'],-1)!='/') $file['name'] .= '/';
                            make_directory('.../'.$file['name']);
                            chmod(makepath('.../'.$file['name']),$file['mode']);
                        }
                    }
                    
                    // Files
                    $tar->seek(0);
                    $first = true;
                    while ( ($first) || ($tar->next()) ) {
                        $first = false;
                        $file = $tar->data();
                        if ($file['type']==0) {
                            if (substr($file['name'],0,1)=='/') $file['name'] = substr($file['name'],1);
                            
                            $write = load_file('.../'.$file['name']);
                            $write->write($tar->curfile());
                            $write->close();
                            
                            chmod(makepath('.../'.$file['name']),$file['mode']);
                        }
                    }
                    
                    $tar->close();
                    $tar = load_file('.../storage/tmp/update'.$id.'.tar');
                    $tar->delete();
                    
                    if (!in_array($update['aflag'],$upgradeflags)) $upgradeflags[] = $update['aflag'];
                    $message = 'updateinstalled';
                    break;
            }
        }
        
        $this->silence();
        $this->cpu('setting','edit','upgradeflags',array('value'=>$upgradeflags));
        $this->speak();
        
        $updatelist = implode(',',$updatelist);
        
        if ($updatelist) {
            $this->genform(array(
                array('type'=>'html','html'=>'<p class="notice">'.get_lang('config',$message).'</p>'),
                'updatelist'=>array('type'=>'hidden','id'=>'updatelist','name'=>'updatelist','value'=>$updatelist),
            ),array(),array('img'=>'web','title'=>get_lang('config','applyupdates')));
        } else {
            if ($file = load_file('tmp:///updates',true)) $file->delete();
            $this->addline('<p class="notice">'.get_lang('config','updatedone').'</p>');
            $this->good('updatedone');
            $this->d_main();
        }
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Uninstall ComicCMS
    ////////////////////////////////////////////////////////////
    function d_uninstall() {
        $this->newpage('uninstall');
        
        $this->warning('uninstallwarn');
        $fields = array('password'=>'password');
        $this->genform($fields,array(),array('img'=>'editdelete'));
    }
    function p_uninstall($data=false) {
        if (!$this->readform($data,array('password'=>'password'))) return $this->d_uninstall();
        
        if ($this->cpu('auth','encrypt',$data['password']) != $this->cpu('auth','get','user','password')) {
            $this->error('uninstallbadpass');
            return $this->d_uninstall();
        }
        
        import('.../lib/filesystem.php');
        $dir = dir_array('.../',0,false,true);
        while($f = next($dir)) {
            if ( (substr($f,-1) !='.') && (substr($f,-2) != '..') ) {
                if (canfind_dir('.../' . $f)) {
                    $l = dir_array('.../' . $f,0,false,true);
                    foreach ($l as $n) array_push($dir,$f . '/' . $n);
                }
                chmod_file(makepath('.../' . $f),0666);
            }
        }
        
        $this->good('uninstall_good');
        $this->d_main();
    }
}

?>