<?php
import('.../lib/cpu/lib.php');

$GLOBALS['cpu_auth'] = array(
    'loggedin'=>false,
);

class cpu_auth extends cpu_lib {
    var $type = 'auth';
    
    var $db=false;
    function cpu_auth() {
        $this->db = load_db('user');
        
        // Auto-login
        if ( (!$GLOBALS['cpu_auth']['loggedin']) && (isset($_COOKIE['ccms_user'])) ) {
            $id = (int) $_COOKIE['ccms_user'];
            $usercpu = load_cpu('user');
            if ($user = $usercpu->get($id)) {
                if ( ( ($user['session']) && (isset($_COOKIE['ccms_session'])) ) && ($user['session'] == $this->encrypt($_COOKIE['ccms_session'])) ) {
                    if ( ($user['ip']) && ($_SERVER['REMOTE_ADDR']==$user['ip']) ) {
                        $this->setlogin($user,(rand(0,10)>9));
                    }
                }
            }
        }
    }
    
    function encrypt($string) {
        return crypt($string,md5($string));
    }
    
    function setlogin($user,$session=false) {
        $usergroupcpu = load_cpu('usergroup');
        $usergroup = $usergroupcpu->get($user['group']);
        
        $GLOBALS['cpu_auth'] = array(
            'loggedin'=>true,
            'user'=>$user,
            'usergroup'=>$usergroup,
        );
        
        if ($session) {
            $ses = $this->encrypt(rand('111111','999999'));
            $this->db->save(array('session'=>$this->encrypt($ses),'ip'=>$_SERVER['REMOTE_ADDR']),$user['id']);
            
            setcookie("ccms_user", $user['id'],time()+9999999,'/');
            setcookie("ccms_session", $ses,(true?time()+9999999:false),'/');
        }
    }
    
    
    function get($var=false,$subvar=false,$subsubvar=false) {
        if (!$var) {
            return $GLOBALS['cpu_auth'];
        } elseif (isset($GLOBALS['cpu_auth'][$var])) {
            if (!$subvar) {
                return $GLOBALS['cpu_auth'][$var];
            } elseif (isset($GLOBALS['cpu_auth'][$var][$subvar])) {
                if (!$subsubvar) {
                    return $GLOBALS['cpu_auth'][$var][$subvar];
                } elseif (isset($GLOBALS['cpu_auth'][$var][$subvar][$subsubvar])) {
                    return $GLOBALS['cpu_auth'][$var][$subvar][$subsubvar];
                }else return false;
            } else return false;
        } else return false;
    }
    
    
    function login($username,&$password) {
        $usercpu = load_cpu('user');
        
        // Check username
        if ($userarr = $usercpu->search($username,'name')) {
            $userdat = array_shift($userarr);
            
            // Check password
            if ($this->encrypt($password) == $userdat['password']) {
                $groupcpu = load_cpu('usergroup');
                
                // Check usergroup
                if ($groupdat = $groupcpu->get($userdat['group'])) {
                    // Save login
                    $this->setlogin($usercpu->get($userdat['id']),true);
                } else return 'usergrouperror';
            } else return 'passwordwrong';
        } else return 'usernamewrong';
            
        $password = false;
        return false; // All went well
    }
    
    
    function logout() {
        if ($this->get('loggedin')) $this->db->save(array('session'=>false),$this->get('user','id'));
        $this->logout_super();
        setcookie("ccms_session", '', time()-3600);
    }
    
    
    
    
    
    
    
    
    
    function is_super() {
        if (!$this->get('usergroup','permissions','_superuser')) return false;
        
        session_name('ccms_superuser');
        if (!@session_start()) return false;
        if ( (isset($_SESSION['super'])) && ($_SESSION['super']=='super') && ($_SESSION['user']==$this->get('user','id')) ) {
            session_write_close();
            return true;
        }
        session_write_close();
        return false;
    }
    
    function login_super(&$password) {
        if ($this->encrypt($password) == $this->get('user','password')) {
            if ($this->get('usergroup','permissions','_superuser')) {
                session_name('ccms_superuser');
                session_start();
                $_SESSION['super'] = 'super';
                $_SESSION['user'] = $this->get('user','id');
                session_write_close();
                $this->good('special','superlogingood');
                return true;
            } else return $this->error('admin','usergroupnosuper');
        } else return $this->error('admin','passwordwrong');
        return true;
    }
    
    function refresh_super() {
        if (!@session_name('ccms_superuser')) return false;
        session_start();
        session_write_close();
        return true;
    }
    
    function logout_super() {
        if (!session_name('ccms_superuser')) return false;
        session_start();
        $_SESSION['super'] = false;
        $_SESSION['user'] = false;
        session_write_close();
        return true;
    }
}
?>