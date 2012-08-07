<?php
/////////////////////////////////////////////////////////////////////
//
// This file holds only the template functions that are needed for
// already cache'd pages as a performance booster. Many other
// template functions are in lib/site/template.php
//
// lib/site/template.php should only be imported if the page
// being viewed is not cached
//
/////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////
// Display a template error message
////////////////////////////////////////////////////////////
function tplerr($lang,$info=false) {
    return '<div style="display:block; padding:5px 10px; font-size:20px; background-color:#ecc; color:#d00;">
    ComicCMS Template Error:<br />
    '.($info=='<plain>'?$lang:get_lang('error','tpl_'.$lang,$info)).'
</div>';
}



////////////////////////////////////////////////////////////
// Display the page
////////////////////////////////////////////////////////////
function show_templatepage($fileid,$pageid,$info) {
    $cachedb = load_db('quickcache');
    
    // Rebuild Header/Footer (if needed) before the page
    if ( ($info['e']) && (!$tops = $cachedb->get_globals()) ) {
        import('.../lib/site/template.php');
        $tclass = new site_template;
        $tops = $tclass->generate_globals();
    }
    
    // Rebuild page if need be
    if (!$tpl = $cachedb->get('tpl_'.$fileid,$pageid,searchid($info['s']))) {
        import('.../lib/site/template.php');
        $tclass = new site_template;
        $tpl = $tclass->generate_page($fileid,$pageid,searchid($info['s']));
    }
    
    // Magic variables
    $vars = array(
        'magic_executetime'=>substr(mtime()-$GLOBALS['executiontime'],0,5),
    );
    
    // All cache'd, make page
    header('content-type: ' . $info['c'].'; charset=UTF-8');
    ob_start();
    if ($info['e']) {
        eval('$vars = array_merge($vars,'.var_export($tpl['vars'],true).'); ?'.'>'.$tops['header']."\n".$tpl['content']."\n".$tops['footer']);
    } else eval('?'.'>'.$tpl['content']);
    $file = ob_get_contents();
    ob_end_clean();
    $file = tpl_parse_cache($file);
    
    // Off we go
    echo $file;
    exit;
}



////////////////////////////////////////////////////////////
// Get environment variables
////////////////////////////////////////////////////////////
function cvar($source) {
    if (substr($source,0,1)=='G') {
        // Capture _GET variable
        return (isset($_GET[substr($source,1)])?$_GET[substr($source,1)]:false);
    } elseif (substr($source,0,1)=='P') {
        // Capture _POST variable
        return (isset($_POST[substr($source,1)])?$_POST[substr($source,1)]:false);
    } elseif (substr($source,0,1)=='D') {
        return date(substr($source,1));
    }
    return $source;
}



////////////////////////////////////////////////////////////
// ID hash for cache file
////////////////////////////////////////////////////////////
function searchid($range) {
    if (!$range) return 0;
    foreach ($range as $k => $v) {
        $range[$k] = (int) cvar($v);
    }
    $range = implode('-',$range);
    return ($range?$range:0);
}



////////////////////////////////////////////////////////////
// Generate Header/Footer
////////////////////////////////////////////////////////////
function tpl_parse_global($global,$vars) {
    // Only does simple variable replace
    $vars = array_merge($global['vars'],$vars);
    
    // Operators (e.g. :rich) and if statements are done before passing to the globals
    $global['content'] = preg_replace("/(\{\{)([A-Za-z0-9_]*)(\}\})/e","((isset(\$vars[\\2]))?\$vars[\\2]:'')",$global['content']);
    // Dynamic ifs are compiled as PHP in the global's cache so we need the :bool operator
    $global['content'] = preg_replace("/(\{\{)([A-Za-z0-9_]*)(:bool)(\}\})/e","(( (isset(\$vars[\\2])) && (\$vars[\\2]) )?1:0)",$global['content']);
    
    return $global['content'];
}



////////////////////////////////////////////////////////////
// Apply rules to cache
////////////////////////////////////////////////////////////
function tpl_parse_cache($file) {
    // Plugin caches
    $file = preg_replace("/(\{\{plugincache\:)([a-zA-Z0-9_]*)([|]?)([a-zA-Z0-9_\.]*)?(\}\})/e","tpl_plugincache(\"\\2\",\"\\4\")",$file);
    $file = preg_replace("/(\{\{pluginnocache\:)([a-zA-Z0-9_]*)([|]?)([a-zA-Z0-9_\.]*)?(\}\})/e","tpl_pluginregen(\"\\2\",\"\\4\")",$file);
    
    return $file;
}



////////////////////////////////////////////////////////////
// Find plugin cache
////////////////////////////////////////////////////////////
function tpl_plugincache($plugin,$extra=false) {
    $cachedb = load_db('quickcache');
    if ($return = $cachedb->get('pl_'.$plugin,$extra)) return $return['content'];
    return tpl_pluginregen($plugin,$extra);
}



////////////////////////////////////////////////////////////
// Regenerate plugin cache
////////////////////////////////////////////////////////////
function tpl_pluginregen($plugin,$extra=false) {
    if (!$class = load_plugin($plugin)) return tplerr('pluginbad',$plugin);
    if (!method_exists($class,'cacherebuild')) return tplerr('pluginnomethod',$plugin . '->cacherebuild');
    return $class->cacherebuild($extra);
}

?>
