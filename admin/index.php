<?php
/********************************************************************
//                      ComicCMS admin panel
//                      ====================
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
define('comiccmsadmin',true);
define('ComicCMS_HQ','http://comiccms.com/HQ/');
if (!@include_once('../lib/lib.php'))  die('Main library file could not be loaded!');



////////////////////////////////////////////////////////////
// PHP warnings when in debug mode
if (get_config('adminmode')=='debug') ini_set('error_reporting', E_ALL);



////////////////////////////////////////////////////////////
// Initiate admin page outside of ajaxy structure
if (!isset($_GET['type'])) {
    header('Content-Type: text/html; charset=UTF-8');
    if ( (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) && (!strpos($_SERVER['HTTP_USER_AGENT'],'Opera')) && (!isset($_GET['forceie'])) ) {
        import('.../admin/noie.php');
    } else import('.../admin/display.php');
    exit;
}



////////////////////////////////////////////////////////////
// Serve types & include files
switch ($_GET['type']) {
    case 'action':
        import('.../lib/action.php');
        exit;
        break;
    case 'js':
        header('content-type:text/javascript');
        import('.../admin/javascript.php');
        exit;
        break;
    case 'preload':
        import('.../admin/preload.php');
        exit;
        break;
    case 'ajax':
        header('content-type:text/xml');
        break;
    case 'img':
        if (!$img = load_file('tmp:///'.$_GET['id'],true)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $image = getimagesize(makepath($img->uri));
        header('content-type:' . image_type_to_mime_type($image[2]));
        echo $img->read('string');
        exit;
        break;
}



////////////////////////////////////////////////////////////
// Sift out page info
$page = 'main';
if (isset($_GET['p'])) {
    if ($f=strpos($_GET['p'],':')) {
        $section = substr($_GET['p'],0,$f);
        $page = substr($_GET['p'],$f+1);
    } else $section = $_GET['p'];
} else $section = 'main';



////////////////////////////////////////////////////////////
// Load admin section & page
////////////////////////////////////////////////////////////
function load_admin($section,$page='main',$error=false) {
    // Allow subsections
    if (strpos($page,':')) list($section,$page) = explode(':',$page);
    
    // Check & load admin lib
    $section = filesafe($section);
    if (canfind_file('.../lib/admin/'.$section.'.php')) {
        import('.../lib/admin/'.$section.'.php');
        
        // Call section switchboard
        $class = 'admin_'.$section;
        $admin = new $class;
        if ($error) $admin->adderror(get_lang('admin',$error));
        $admin->switchboard($page);
        
        // Return admin page
        return $admin;
    } else return load_admin('main','main','badsection');
}



////////////////////////////////////////////////////////////
// Load admin page
$admin = load_admin($section,$page);
$admin->end();

?>