<?php
// ComicCMS :: Copyright (C) 2007 Steve H
// http://comiccms.com/
// File auto-generated 2012-08-06T21:24:43-04:00
if (!@include_once('/home/amacdougall/projects/_sites/violetprotocol/lib/lib.php')) die('ComicCMS library file could not be loaded!');
import('.../lib/site/page.php');
if ( ($v = cvar('Gfeed')) && ($v == 'news') ) show_templatepage('inc/feed',2,array('s'=>array(0=>'0',1=>'-9',2=>'',),'e'=>0,'c'=>'application/rss+xml',));
show_templatepage('inc/feed',1,array('s'=>array(0=>'0',1=>'-9',2=>'',),'e'=>0,'c'=>'application/rss+xml',));
die('All triggers missed');
?>