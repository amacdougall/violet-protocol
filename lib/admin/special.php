<?php
import('.../lib/admin/lib.php');

class admin_special extends admin_lib {
    var $section = 'special';
    var $navigation = array(
        'search'=>false,
        'login'=>false,
        'install'=>false,
        'updatecheck'=>false,
    );
    
    
    
    function d_install() {
        $this->newpage('install');
        if (get_config('baseurl')=='../') {
            $lib_file = load_file('.../lib/lib.php');
            $file = $lib_file->read('array');
            $file[0] = '<?php $GLOBALS[\'settings\'] = array(\'baseurl\'=>\'http://\'.$_SERVER[\'HTTP_HOST\'].\'' . foldername(dirname($_SERVER['PHP_SELF'])) . '\',\'basepath\'=>dirname(dirname(__FILE__)) . \'/\');'."\n";
            $lib_file->write($file);
        }
        
        /////////////////////
        // Koi hacks
        if ($download = load_file('.../download.php',true)) $download->delete(); // Just-in-case
        
        // IP safety lock from download.php
        if ($lockfile = load_file('.../lock.php',true)) {
            if (rtrim($lockfile->read(0))=='<?php exit; ?' . '>') {
                if ($_SERVER['REMOTE_ADDR']!=$lockfile->read(1)) {
                    $this->error('installlock');
                    $this->end();
                }
            } else $lockfile = false;
        } else $lockfile = false;
        // End Koi hacks
        /////////////////////
        
        $user = $_POST;
        $user['group'] = 1;
        if ( (isset($user['name'])) && ($user['password']==$user['password_v']) ) {
            if ( (isset($_POST['install'])) && ($this->cpu('user','add',$user)) ) {
                // Site file regeneration
                $this->cpu('file','rebuild');
                
                // Plugin Installation
                import('.../lib/filesystem.php');
                $plugins = dir_array('.../lib/plugins/');
                foreach ($plugins as $plugin) {
                    if (!$this->cpu('plugin','get',substr($plugin,0,strlen($plugin)-4))) {
                        $this->cpu('plugin','add',array(),array(
                            'tmp_name'=>'.../lib/plugins/'.$plugin,
                            'name'=>$plugin,
                        ));
                    }
                }
                
                // Un-suppress & finish
                if ($lockfile) $lockfile->delete();
                $this->addline('<p class="notice">'.get_lang('special','installdone').'</p>');
                $this->d_login();
                $this->end();
            }
        }
        
        $this->addline('<h2 id="welcome" style="margin:120px 0px 0px; padding:10px 0px;font-size:50px;line-height:100%;"><span id="hai">Hi there, </span><span id="whozzat">who are you?</span></h2>
        <form id="canhas" action="index.php" method="post" class="fullform" style="background-image:url(img/glance.jpg); background-position:bottom left; background-repeat:no-repeat; background-color:#fff; margin:0px; padding-left:300px; height:380px;">
            <h3>'.get_lang('special','installcreate').'</h3>
            <label for="username">'.get_lang('special','installusername').'</label><input type="text" name="name" id="username" />
            <label for="password">'.get_lang('special','installpassword').'</label><input type="password" name="password" id="password" />
            <label for="confirm">'.get_lang('special','installpasswordv').'</label><input type="password" name="password_v" id="confirm" />
            
            <input type="submit" name="install" class="save" value="'.get_lang('special','installcontinue').'" />
        </form>');
        
        $this->addjavascript('$(\'hai\').setStyle(\'opacity\',0);
        $(\'whozzat\').setStyle(\'opacity\',0);
        $(\'canhas\').setStyle(\'opacity\',0);
        new Fx.Morph($(\'hai\'), {duration:2500, wait:false, onComplete:function () {
            new Fx.Morph($(\'whozzat\'), {duration:1200, wait:false, onComplete:function () {
                new Fx.Morph($(\'welcome\'), {duration:900, wait:false}).start({\'margin-top\': 0, \'font-size\': \'26px\'});
                new Fx.Morph($(\'canhas\'), {duration:1400, wait:false}).start({\'opacity\': 1});
            }}).start({\'opacity\': 1});
        }}).start({\'opacity\': 1});');
        
        $this->end();
    }
    
    
    
    
    function d_login($username=false) {
        $this->newpage('login');
        
        $this->addline('<div style="position:relative; width:900px; height:300px; text-align:center; margin:0 auto;">');
        
        // Background blanket
        if ( (function_exists('gd_info')) && ($comics = $this->cpu('comic','get','batch','-0','-11')) && (count($comics)>5) ) {
            foreach ($comics as $comic) {
                $this->addline('<div style="height:150px;width:150px;text-align:center; float:left; opacity:0.4;"><img src="img/thumb/'.$comic['id'].'.jpg" style="width:100%; height:100%;" alt="" /></div>');
            }
        } else {
            $this->addline('<img src="img/loginblanket.png" alt="" />');
        }
        $this->addline('<div style="position:absolute; top:0px; left:0px; text-align:center; width:100%;"><img src="img/loginhaze.png" alt="" /></div>');
        
        
        // Start Form
        $file = 'index.php';
        if ($p = strpos($_SERVER['REQUEST_URI'],'?')) $file .= htmlspecialchars(substr($_SERVER['REQUEST_URI'],$p));
        $this->addline('  <div style="position:absolute; top:0px; left:0px; width:100%; padding-top:70px; text-align:center;background-image:url(http:///admin/img/login.png);background-repeat:no-repeat;background-position:center center; height:250px;">');
        $this->addline('    <form action="' . $file . '" method="post" id="loginform" style="position:relative;">');
        
        
        // Re-submit dialogue
        if (isset($_POST['save'])) {
            $this->addline('  <div id="resubmit_msg" style="background-color:#fff; border:solid 2px #fda; position:absolute; height:160px;">');
            $this->addline('      <p>'.get_lang('special','login_repost').'</p>');
            $this->addline('      <br /><input type="button" value="'.get_lang('special','login_repost_yes').'" style="width:40%;" onclick="$(\'resubmit_msg\').setStyle(\'display\',\'none\'); $(\'login_resubmit\').value=\'1\';" />');
            $this->addline('      <input type="button" value="'.get_lang('special','login_repost_no').'" style="width:40%;" onclick="$(\'resubmit_msg\').setStyle(\'display\',\'none\');" />');
            $this->addline('      <input type="hidden" name="login_resubmit" id="login_resubmit" value="0" />');
            $this->addline('  </div>');
        }
        
        // End Form
        $this->addline('      <label for="username">'.get_lang('special','login_username').'</label><input type="text" name="username" id="username" class="text" value="' . $username . '" />');
        $this->addline('      <label for="userpass">'.get_lang('special','login_password').'</label><input type="password" name="userpass" id="userpass" class="text" />');
        $this->addline('      <input type="submit" class="button" name="login" value="'.get_lang('special','login').'" />');
        $this->addline('    </form>');
        $this->addline('  </div>');
        
        $this->addline('</div>');
        
        if (isset($_POST['save'])) $this->addjavascript('$(\'loginform\').addEvent(\'submit\',function (e) {
            login_a(e,$(\'loginform\'),$(\'login_resubmit\').value,$(\'username\').value,$(\'userpass\').value);
        });');
        
        $this->end();
    }
    function p_login() {return $this->d_login();}
    
    
    
    
    
    function f_login_super() {
        return array(
            'password'=>'password',
            'section'=>'hidden',
            'page'=>'hidden',
        );
    }
    function d_login_super($data=false) {
        if (!$this->cpu('auth','get','usergroup','permissions','_superuser')) {
            $this->newpage('login_super');
            $this->warning('usergroupnosuper');
            $this->end();
        }
        
        setvar($data,'p');
        
        $this->newpage('login_super');
        
        $this->genform($this->f_login_super(),array('section'=>$data['section'],'page'=>$data['page']),array('img'=>'password'));
        
        $this->end();
    }
    function p_login_super($data=false) {
        if (!$this->cpu('auth','get','usergroup','permissions','_superuser')) {
            $this->newpage('login_super');
            $this->error('usergroupnosuper');
            $this->end();
        }
        
        if (!$this->readform($data,$this->f_login_super())) return $this->d_login_super();
        
        if (!$this->cpu('auth','login_super',$data['password'])) return $this->d_login_super($data);
        
        $section = ($data['section']?$data['section']:'main');
        $page = ($data['page']?$data['page']:'main');
        
        unset($_POST['save']);
        load_admin($section,$page);
    }
    
    
    function d_refresh_super() {
        $this->cpu('auth','refresh_super');
        $this->end();
    }
    
    
    function d_logout_super() {
        $this->cpu('auth','logout_super');
        $this->end();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    function d_updatecheck() {
        if ($this->cpu('auth','get','usergroup','permissions','_checkupdate')) {
            $download = load_class('download');
            $this->addline($download->download(constant('ComicCMS_HQ').'updates.php?type=bool&version='.urlencode(constant('ComicCMS')).'&upgradeflags='.urlencode(@implode(',',get_config('upgradeflags')))));
        }
    }
    
    
    
    
    
    
    
    
    function f_editself() {
        return array(
            'name'=>'text',
            'password'=>'password_v',
            'email'=>'text',
        );
    }
    function d_editself($user=array()) {
        $this->newpage('editself');
        
        if (!$this->cpu('auth','get','loggedin')) return false;
        
        $user = $this->cpu('auth','get','user');
        
        $this->addline('<img src="'.$user['gravatar'].'&amp;s=70" style="float:right; width:70px; position:relative; top:-15px;" />');
        $this->genform($this->f_editself(),$user,array(
            'img'=>'identity',
        ));
    }
    function p_editself($user=array()) {
        if (!$this->readform($user,$this->f_editself())) return $this->d_editself($user);
        
        if (!$this->cpu('user','edit',$this->cpu('auth','get','user','id'),$user)) return $this->d_editself($user);
        
        unset($_POST['save']);
        load_admin('main','main');
    }
}
?>