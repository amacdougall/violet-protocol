<?php
if (!defined('comiccmsadmin')) die();

function obcallback($buffer) {
    return str_replace('http:///',get_config('baseurl'),$buffer);
}
ob_start('obcallback');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>ComicCMS Admin Panel</title>
    <!-- Mootools -->
    <script type="text/javascript" src="inc/mootools.js"></script>
    <!-- Alerts (Roar) -->
    <script type="text/javascript" src="inc/roar/Roar.js"></script>
    <link type="text/css" rel="stylesheet" href="inc/roar/Roar.css" />
    
    <!-- In house -->
    <script type="text/javascript" src="?type=js"></script>
    <link type="text/css" rel="stylesheet" href="admin.css" />
    
    <!-- Nifty corners -->
    <script type="text/javascript" src="inc/nifty/nifty.js"></script>
    <link type="text/css" rel="stylesheet" href="inc/nifty/nifty.css" />
    
    <meta name="generator" content="ComicCMS" />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="robots" content="NOINDEX, NOFOLLOW" />
    <link href="img/dini.png" rel="shortcut icon" />
    <link href="img/dini.gif" rel="icon" />
</head>
<body id="body">
<div id="topbox">
    <div id="honey">
        
        <div id="logolink">
            <a href="http://comiccms.com/" title="ComicCMS" target="_blank"><img src="img/linklogo.png" alt="ComicCMS" /></a>
        </div>
        
        <div id="imgbar">
            <img src="img/sectionspace.png" alt="" />
            
            <a href="http:///admin/" id="section_main" title="<?php echo get_lang('title','sectionmain'); ?>"><img src="img/crystal/blockdevice.png" alt="<?php echo get_lang('title','sectionmain'); ?>" /></a>
            <a href="?p=comic" id="section_comic" title="<?php echo get_lang('title','sectioncomic'); ?>"><img src="img/crystal/krita.png" alt="<?php echo get_lang('title','sectioncomic'); ?>" /></a>
            <a href="?p=news" id="section_news" title="<?php echo get_lang('title','sectionnews'); ?>"><img src="img/crystal/knode.png" alt="<?php echo get_lang('title','sectionnews'); ?>" /></a>
            <div id="superallow">
                <a href="?p=plugin" id="section_plugin" title="<?php echo get_lang('title','sectionplugin'); ?>"><img src="img/crystal/warehause.png" alt="<?php echo get_lang('title','sectionplugin'); ?>" /></a>
                <a href="?p=template" id="section_template" title="<?php echo get_lang('title','sectiontemplate'); ?>"><img src="img/crystal/background.png" alt="<?php echo get_lang('title','sectiontemplate'); ?>" /></a>
                <a href="?p=config" id="section_config" title="<?php echo get_lang('title','sectionconfig'); ?>"><img src="img/crystal/kservices.png" alt="<?php echo get_lang('title','sectionconfig'); ?>" /></a>
            </div>
            
            <img src="img/sectionspace.png" alt="" />
        </div>
        
        <div id="admin_title">
            <div id="topbar">
                <a href="?type=logout" id="logoutlink" title="Logout" target="_self"><img src="img/crystal/hibernate.png" alt="Logout" /></a>
                <a href="http:///" id="sitelink" title="Back to site"><img src="img/crystal/web.png" alt="Back to site" /></a>
                <a href="javascript:showconnecting();" id="connectionstatus" title="<?php echo get_lang('admin','connecting');?>"><img src="img/connecting.gif" alt="ComicCMS Connection" /></a>
                <a href="javascript:showsuperuser();" id="superuserstatus" title="<?php echo get_lang('admin','superuser');?>"><img src="img/crystal/password.png" alt="Superuser" /></a>
            </div>
            
            <a href="javascript:window.frames['iloadframe'].location.reload(true)" title="Refresh" id="refresh"></a>
            
            <h1><span id="titletext"><?php echo get_lang('admin','loading'); ?></span></h1>
        </div>
        
        
        <div id="msg_hold"></div>
    </div>
</div>
<div id="contentbox">
    <iframe src="index.php?type=inline<?php if ($p = strpos($_SERVER['REQUEST_URI'],'?')) echo '&amp;'.htmlspecialchars(substr($_SERVER['REQUEST_URI'],$p+1)); ?>" name="iloadframe" id="iloadframe">Error: You need iframes enabled in order to use ComicCMS</iframe>
    <div id="inlinepage">
        <noscript><div class="errmsg"><h3><?php echo get_lang('admin','error'); ?></h3><p><?php echo get_lang('admin','nojs'); ?></p></div></noscript>
    </div>
    <p style="clear:both"></p>
    
    <div id="superuserbox">
        <p><?php echo get_lang('admin','superuserex');?></p>
        <a href="?p=special:logout_super&amp;type=ajax" onclick="hidesuperuser(); isnotsuperuser();" style="float:left;" target="preloadframe"><?php echo get_lang('admin','superuserlogout'); ?></a>
        <a href="javascript:hidesuperuser();" style="float:right;"><?php echo get_lang('admin','hide'); ?></a>
    </div>
    
    
</div>

<div id="connectingbox">
    <p><?php echo get_lang('admin','connectingex');?></p>
    <a href="<?php echo constant('ComicCMS_HQ');?>info.php" style="float:left;" target="_blank"><?php echo get_lang('admin','connectwhat'); ?></a>
    <a href="javascript:hideconnecting();" style="float:right;"><?php echo get_lang('admin','hide'); ?></a>
</div>


<iframe src="index.php?type=preload" name="preloadframe" id="preloadframe"></iframe>

</body>
</html>
<?php ob_end_flush(); ?>