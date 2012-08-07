<?php
$imgs = array('connected.gif','connectfail.png','move.png','refresh_h.png','reset.gif','x.png','topback.png','topback_r.png',);

// All crystal icons
$dirlist = opendir(makepath('.../admin/img/crystal/'));
while ( ($file = readdir($dirlist)) !== false) {
    if (strpos($file,'.png')) $imgs[] = 'crystal/'.$file;
}

foreach ($imgs as $img) echo '<img src="'.get_config('baseurl').'admin/img/'.$img.'" alt="" />';
?>