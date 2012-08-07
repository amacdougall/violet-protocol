<?php $GLOBALS['settings'] = array('baseurl'=>'http://'.$_SERVER['HTTP_HOST'].'/violetprotocol/','basepath'=>dirname(dirname(__FILE__)) . '/');
// Those are the base server settings, they are dynamic and should work for all servers.
// If they are not working then uncomment the following line(s) to override them manualy:
//    $GLOBALS['settings']['baseurl'] = 'http://example.com/comiccms/'; // NEEDS THE TRAILING SLASH!
//    $GLOBALS['settings']['basepath'] = '/home/user/htdocs/comiccms/'; // NEEDS THE TRAILING SLASH! Replace all backslash (\) with forwardslash (/)


/********************************************************************
//                     ComicCMS main library
//                     =====================
//                   Copyright (C) 2007 Steve H
//                      http://ComicCMS.com/
//-------------------------------------------------------------------
//  This program is free software; you can redistribute it and/or
//  modify it under the terms of the GNU General Public License
//  as published by the Free Software Foundation; either version 3
//  of the License, or (at your option) any later version.
//  
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//  
//  http://www.gnu.org/copyleft/gpl.html
//
********************************************************************/
if ( (!defined('supress')) && ( (!$GLOBALS['settings']['basepath']) || (!is_file($GLOBALS['settings']['basepath'].'lib/lib.php')) ) ) die('basepath error - This sure is unexpected, please <a href="http://comiccms.com/forum/">tell me</a>!');

define('ComicCMS','0.2');

////////////////////////////////////////////////////////////
// Strip those pesky slashes
if (get_magic_quotes_gpc()) {
    $_POST = array_map('stripslashes', $_POST);
    $_GET = array_map('stripslashes', $_GET);
}

////////////////////////////////////////////////////////////
// Load & execute cron tasks
if (!defined('suppress')) {
    $crondb = load_db('quickcron',true);
    if ($crondb->executeflag()) {
        $croncpu = load_cpu('cron');
        $croncpu->execute();
    }
    unset($crondb);
}

////////////////////////////////////////////////////////////
// I can't find a standard spec for this in HTTP but the
//+ generator type is often used in meta tags so just let
//+ me have some fame here ;)
//
header('Generator: ComicCMS');

////////////////////////////////////////////////////////////
// Keep track of included lib files (for advanced merging)
$GLOBALS['included'] = array('.../lib/lib.php');

// Execution time:
$GLOBALS['executiontime'] = mtime();

/*******************************************************************/
/************** end sequential code, start functions ***************/
/*******************************************************************/

function mtime() {
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    return $mtime;
}


////////////////////////////////////////////////////////////
// General filesystem functions
////////////////////////////////////////////////////////////
// Convert internal code into absolute path
function makepath($path='.../') {
    if (substr($path,0,4)=='.../') $path = realpath(get_config('basepath')) . '/' . substr($path,4);
    return str_replace(array('\\','//'),'/',$path);
}
// Last resort to stop hacks to the filesystem
function filesafe($file) {return str_replace(array('/','\\'),'_',$file);}
function foldersafe($folder) {return str_replace(array('/','\\','.'),'',$folder);}
function pathsafe($path,$extra='') {
    if (strpos(absolute_path($path),foldername(get_config('basepath')).$extra)===0) return true;
    return false;
}
// makepath() wrappers
function canfind($path) {return (( (canfind_file($path)) || (canfind_dir($path)) )?true:false);}
function canfind_file($path) {return is_file(makepath($path));}
function canfind_dir($path) {return is_dir(makepath($path));}
function canread($path) {return is_readable(makepath($path));}
function canwrite($path) {return is_writable(makepath($path));}
// OS transparency
function foldername($path) {
    return str_replace(array('\\','//'),'/',makepath(substr($path,-1)=='/'?$path:dirname($path).'/'));
}
function filename($path) {return basename($path);}
function absolute_path($check) {
    $check = foldername(dirname(makepath($check).'/').'/');
    $check = explode('/',$check);
    foreach ($check as $k => $c) {
        switch ($c) {
            case '.':
                unset($check[$k]);
                break;
            case '..':
                unset($check[$k]);
                $x = $k-1;
                while ( (!isset($check[$x])) && ($x>=0) ) $x--;
                if ($x>=0) {
                    unset($check[$x]);
                } else return false;
        }
    }
    return implode('/',$check);
}



////////////////////////////////////////////////////////////
// Cleanup inputted text
////////////////////////////////////////////////////////////
function plaintext($inp) {
    if (is_array($inp)) {
        foreach ($inp as $key => $value) $inp[$key] = plaintext($value);
        return $inp;
    }
    return translate(str_replace(array('{','}'),array('&#123;','&#125;'),htmlspecialchars($inp,ENT_QUOTES)));
}
function simpletext($inp) {
    if (is_array($inp)) {
        foreach ($inp as $key => $value) $inp[$key] = simpletext($value);
        return $inp;
    }
    return preg_replace('/([^0-9a-zA-Z-_\ ])/i','',$inp);
}
function translate($inp) {
    if (is_array($inp)) {
        foreach ($inp as $key => $value) $inp[$key] = translate($value);
        return $inp;
    }
    if (!isset($GLOBALS['settings']['trans'])) {
        $table = get_html_translation_table(HTML_ENTITIES);
        unset($table[' '],$table['"'],$table['<'],$table['>'],$table['&']);
        $GLOBALS['settings']['trans'] = $table;
    }
    return strtr($inp,get_config('trans'));
}
function templatetext($type,$inp) {
    if (is_array($inp)) {
        foreach ($inp as $key => $value) $inp[$key] = templatetext($type,$value);
        return $inp;
    }
    if ($type=='store') {
        return str_replace(get_config('baseurl'),'http:///',$inp);
    } elseif ($type=='unstore') {
        return str_replace('http:///',get_config('baseurl'),translate($inp));
    }
    return false;
}
// Passwords
function encrypt($string) {return crypt($string,md5($string));}




////////////////////////////////////////////////////////////
// Grab language string
////////////////////////////////////////////////////////////
function get_lang($group,$item,$extra=false,$slash=false,$bool=false) {
    if (!class_exists('language')) import('.../lib/lang.php');
    if (!isset($GLOBALS['langcache'][$group])) {
        $lang = new language;
        $GLOBALS['langcache'][$group] = (isset($lang->$group)?$lang->$group:false);
    }
    $extra = ($extra?plaintext($extra):$extra);
    
    $s=false;
    if ( (is_array($GLOBALS['langcache'][$group])) && (isset($GLOBALS['langcache'][$group][$item])) ) {
        $froms = array('{{HQ}}','{{forum}}','{{GPL}}','{{startwiki}}','{{endwiki}}');
        $tos = array('<a href="http://comiccms.com/" target="_blank">ComicCMS.com</a>','<a href="http://comiccms.com/forum/" target="_blank">ComicCMS forums</a>','<a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU GPL 3</a>','<a href="http://wiki.comiccms.com/','" target="_blank">[wiki page]</a>');
        $message = str_replace($froms,$tos,$GLOBALS['langcache'][$group][$item]);
        $s=true;
    } elseif ($bool) {
        return false;
    } elseif ( ($lang = new language) && (isset($lang->error['L0'])) ) {
        $message = $lang->error['L0'] . ($extra!==false?' :: Info=[' . $extra . ']':'');
        $extra = $group . ' - ' . $item;
    } else {
        $message = 'Language code not found (' . $group . ' - ' . $item . ')' . ($extra!==false?' :: Info=[' . $extra . ']':'');
    }
    $message = ($extra!==false?str_replace('{{info}}',$extra,$message):$message);
    $message = ($slash?str_replace(array('\'','"'),array('\\\'','\"'),$message):$message);
    if ( (get_config('adminmode')=='translate') && (defined('adminstart')) && (!is_array($message)) && ($s) ) {
        return '[[' . $group . ' - ' . $item . ']]' . $message . '^';
    }
    return $message;
}



////////////////////////////////////////////////////////////
// Grab setting value
////////////////////////////////////////////////////////////
function get_config($item) {
    if (isset($GLOBALS['settings'][$item])) {
        return $GLOBALS['settings'][$item];
    } elseif ($item == 'version') {
        return constant('ComicCMS');
    } elseif (substr($item,0,5)=='lang_') {
        $item = substr($item,5);
        if (!class_exists('language')) import('.../lib/lang.php');
        $lang = new language;
        $GLOBALS['settings']['lang_'.$item] = (@isset($lang->$item)?$lang->$item:false);
        return $GLOBALS['settings']['lang_'.$item];
    } else {
        if (!isset($GLOBALS['settings']['version'])) {
            $settingdb = load_db('setting');
            $settings = $settingdb->get();
            if (parse_dbresult($settings)) {
                foreach ($settings as $setting) {
                    $GLOBALS['settings'][$setting['name']] = $setting['value'];
                }
            }
        }
        return (isset($GLOBALS['settings'][$item])?$GLOBALS['settings'][$item]:false);
    }
}



////////////////////////////////////////////////////////////
// Process database result
////////////////////////////////////////////////////////////
function parse_dbresult(&$result,$plaintext=false,$nolang=false) {
    if (isset($result['debug'])) {
        if (isset($GLOBALS['adminpage'])) $GLOBALS['adminpage']->adddebug($result['debug']);
        unset($result['debug']);
    }
    $result = ($plaintext?plaintext($result):$result);
    if (!isset($result['result'])) {
        //if (isset($GLOBALS['adminpage'])) $GLOBALS['adminpage']->adddebug('No result tag returned by db function');
        return $result;
    }
    switch ($result['result']) {
        case 'good':
            unset($result['result']);
            break;
        case 'error':
            $result = false;
            break;
        case 'empty':
            $result = array();
            break;
        default:
            if (isset($GLOBALS['adminpage'])) $GLOBALS['adminpage']->adddebug('Bad result returned by db function (' . $result['result'] . ')');
            break;
    }
    return $result;
}



////////////////////////////////////////////////////////////
// Include PHP file
////////////////////////////////////////////////////////////
function import($file) {
    if (in_array($file,$GLOBALS['included'])) return true;
    if (@!include_once(makepath($file))) {
        if (!canfind_file($file)) fatal_error('findfile',$file);
        if (!canread($file)) fatal_error('readfile',$file);
        return false;
    }
    $GLOBALS['included'][] = $file;
    return true;
}



////////////////////////////////////////////////////////////
// Load database module
////////////////////////////////////////////////////////////
function load_db($module,$poly=false) {
    if ( ($poly) || (!isset($GLOBALS['dbclass'][$module])) ) {
        import('.../lib/db/file/'.filesafe($module).'.php');
        $class = 'db_'.$module;
        $class = new $class;
        
        // Just return class if wanted
        if ($poly) return $class;
        
        // Create & Return memlink to global class
        $GLOBALS['dbclass'][$module] = $class;
    }
    $r = &$GLOBALS['dbclass'][$module];
    return $r;
}



////////////////////////////////////////////////////////////
// Load CPU module
////////////////////////////////////////////////////////////
function load_cpu($module) {
    import('.../lib/cpu/'.filesafe($module).'.php');
    $class = 'cpu_'.$module;
    $class = new $class;
    return $class;
}



////////////////////////////////////////////////////////////
// Load lib class
////////////////////////////////////////////////////////////
function load_class($class) {
    import('.../lib/class/'.filesafe($class).'.php');
    $class = 'class_'.$class;
    $class = new $class;
    return $class;
}



////////////////////////////////////////////////////////////
// Useful strrpos() replacement for PHP 4
////////////////////////////////////////////////////////////
function strlpos($h,$n,$o=0) {
    $p = strpos(strrev($h),strrev($n),$o);
    return ($p===false?$p:(strlen($h)-$p-strlen($n)));
}



////////////////////////////////////////////////////////////
// Destructable mem cache
////////////////////////////////////////////////////////////
function addmem($name,$contents) {
    if (!isset($GLOBALS['comiccmsmem'])) $GLOBALS['comiccmsmem'] = array();
    $GLOBALS['comiccmsmem'][$name] = $contents;
}
function getmem($name) {
    return (isset($GLOBALS['comiccmsmem'][$name])?$GLOBALS['comiccmsmem'][$name]:false);
}
function delmem($name=false) {
    if ( ($name) && (isset($GLOBALS['comiccmsmem'][$name])) ) {
        unset($GLOBALS['comiccmsmem'][$name]);
    } elseif ( (!$name) && (isset($GLOBALS['comiccmsmem'])) ) {
        unset($GLOBALS['comiccmsmem']);
    }
}



////////////////////////////////////////////////////////////
// Read/print out text file with optional simple templates
////////////////////////////////////////////////////////////
function print_file($path,$templates=false,$print=true,$plaintext=false) {
    $file = file_string($path);
    if ($file === false) return false;
    if ($plaintext) $file = plaintext($file);
    $file = str_replace('http:///',get_config('baseurl'),$file);
    
    if (is_array($templates)) {
        foreach($templates as $from => $to) $file = str_replace('{{' . $from . '}}',$to,$file);
    }
    
    if ($print) {
        echo $file;
        return true;
    }
    return $file;
}



////////////////////////////////////////////////////////////
// Return file contents
////////////////////////////////////////////////////////////
function load_file($uri,$check=false,$poly=false) {
    // Support temporary files
    if (substr($uri,0,7)=='tmp:///') {
        $tmpdb = load_db('tmp');
        return $tmpdb->get($uri,$check);
    }
    
    // Check file exists
    if ( ($check) && (!canfind_file($uri)) ) return false;
    
    // Check for memcache
    if ( ($poly) || (!isset($GLOBALS['fileclass'][makepath($uri)])) ) {
        $file = new file($uri);
        
        // Just return class if wanted
        if ($poly) return $file;
        
        // Create & Return memlink to global class
        $GLOBALS['fileclass'][makepath($uri)] = $file;
    }
    $r = &$GLOBALS['fileclass'][makepath($uri)];
    return $r;
}
class file {
    var $uri = false;
    var $content = false;
    var $data = array();
    
    // __construct() and reset cache
    function file($uri=false) {
        $this->content = false;
        
        if ($uri) $this->uri = $uri;
    }
    
    
    function close() {
        $this->content = false;
        $this->data = false;
        unset($GLOBALS['fileclass'][$this->uri]);
        $this->uri = false;
    }
    
    
    function read($type=false) {
        // Load file
        if ($this->content===false) {
            if (!canfind_file($this->uri)) fatal_error('findfile',$this->uri);
            if (!canread($this->uri)) fatal_error('readfile',$this->uri);
            $this->content = file(makepath($this->uri));
        }
        
        // Return content
        if (is_numeric($type)) {
            if (isset($this->content[$type])) {
                return $this->content[$type]; // One line
            } else return '';
        }
        if ($type=='array') return $this->content; // As array
        if ($type=='string') return implode($this->content); // As string
        return true;
    }
    
    
    
    function make_directory($chmod=false) {
        import('.../lib/filesystem.php');
        make_directory($this->uri,$chmod);
    }
    function write($file,$chmod=false) {
        import('.../lib/filesystem.php');
        write_file($this->uri,'w',$file,$chmod);
        $this->file();
    }
    function append($file,$chmod=false) {
        import('.../lib/filesystem.php');
        write_file($this->uri,'a',$file,$chmod);
        $this->file();
    }
    
    // Copy from
    function copy($file,$chmod=false) {
        import('.../lib/filesystem.php');
        copy_file($file,$this->uri,$chmod);
        $this->file();
    }
    // Move to
    function move($file,$chmod=false) {
        import('.../lib/filesystem.php');
        move_file($this->uri,$file,$chmod);
        $this->file($file);
    }
    // Rename to
    function rename($name,$chmod=false) {
        $this->move(foldername($this->uri).filename($name),$chmod);
    }
    
    function delete() {
        import('.../lib/filesystem.php');
        delete_file($this->uri);
        $this->file();
    }
}

// Legacy functions
function file_array($file) {
    $file = load_file($file);
    return $file->read('array');
}
function file_string($file) {
    $file = load_file($file);
    return $file->read('string');
}



////////////////////////////////////////////////////////////
// Apply JSON to PHP 4
////////////////////////////////////////////////////////////
if (!function_exists('json_decode')) {
    function json_decode($content,$assoc=false) {
        import('.../lib/class/json.php');
        if ($assoc) {
            $json = new class_json(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new class_json;
        }
        return $json->decode($content);
    }
}
if (!function_exists('json_encode')) {
    function json_encode($content) {
        import('.../lib/class/json.php');
        $json = new class_json;
        return $json->encode($content);
    }
}



////////////////////////////////////////////////////////////
// Plugin handler
////////////////////////////////////////////////////////////
function load_plugin($plugin) {
    $id = filesafe(is_array($plugin)?$plugin['name']:$plugin);
    if (!canfind_file('.../lib/plugins/' . $id . '.php')) return false;
    import('.../lib/plugin.php');
    import('.../lib/plugins/' . $id . '.php');
    $class = 'pl_' . $id;
    $class = new $class;
    $class->load($plugin);
    return $class;
}



////////////////////////////////////////////////////////////
// Report fatal error to user
////////////////////////////////////////////////////////////
function fatal_error($lc,$extra=false) {
    $message = false;
    
    if (!@include_once(makepath('.../lib/lang.php'))) {
        if (!@include_once('./lib/lang.php')) {
            if (!@include_once('../lib/lang.php')) {
                $message = 'Error: language file could not be loaded (Original error code: <b>' . plaintext($lc) . '</b>)' . ($extra?'<br />extra = <b>' . plaintext($extra) . '</b>':'');
            }
        }
    }
    
    if (!$message) {
        $lang = new language;
        $message = (isset($lang->error[$lc])?$lang->error[$lc]:'Error: language code not found (<b>' . plaintext($lc) . '</b>)' . ($extra?'<br />extra = <b>' . plaintext($extra) . '</b>':''));
        
        if ($extra) $message = str_replace('{{info}}',$extra,$message);
    }
    
    echo '<html><head><title>500 Internal Server Error</title>' . "\n"
       . '<style type="text/css">' . "\n"
       . '  body{margin:0px; background-color:#ffffdd; text-align:center;}' . "\n"
       . '  h2{margin:15% 0% 0% 0%; color:#75202d; background-color:#ddddbb; font-family:"Lucida Grande", "Lucida Sans Unicode", verdana, lucida, helvetica, sans-serif; font-size:25px;}' . "\n"
       . '  h3{margin:0px; color:#75502d; background-color:#ddddbb; font-family:"Lucida Grande", "Lucida Sans Unicode", verdana, lucida, helvetica, sans-serif; font-size:15px;}' . "\n"
       . '  p{color:#000000;}' . "\n"
       . '</style>' . "\n"
       . '</head><body>' . "\n"
       . '<h2>Uh-Oh, error 500</h2>' . "\n"
       . '<h3>Oops, my bad!</h3>' . "\n"
       . '<p style="text-align:center;">' . $message . '</p>' . "\n"
       . '</body></html>';
    exit;
}

?>