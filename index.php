<?php
// ComicCMS :: Copyright (C) 2007 Steve H
// http://comiccms.com/
// File auto-generated 2012-08-06T21:24:43-04:00
if (!@include_once('/home/amacdougall/projects/_sites/violetprotocol/lib/lib.php')) die('ComicCMS library file could not be loaded!');
import('.../lib/site/page.php');
if ( ($v = cvar('Gid')) && ($v > 0) ) show_templatepage('index',2,array('s'=>array(0=>'Gid',),'e'=>1,'c'=>'text/html',));
show_templatepage('index',1,array('s'=>array(),'e'=>1,'c'=>'text/html',));
die('All triggers missed');
?>