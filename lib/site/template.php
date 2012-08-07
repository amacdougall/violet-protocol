<?php
/********************************************************************
//                   ComicCMS template library
//                   =========================
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
import('.../lib/site/page.php');


// Brief overview:
//   generate_page() brings together the page, items, plugins, links, etc.
//   get() handles the fetching and prev/next linking of items
//   apply() breaks down overall templates/foreach for parse() to handle
//   parse() handles the variable replacements/ifs for one item


class site_template {
    ////////////////////////////////////////////////////////////
    // CPU bridge
    ////////////////////////////////////////////////////////////
    function cpu($cpu,$method) {
        $cpu = load_cpu($cpu);
        
        // No call_user_func_array() for classes (PHP 4)
        // So here's an ugly hack
        $args = func_get_args();
        array_shift($args); array_shift($args);
        if ($args) {
            eval('$result = $cpu->$method($args['.implode('],$args[',array_keys($args)).']);');
        } else $result = $cpu->$method();
        
        // Spout any messages given
        foreach ($cpu->messages['error'] as $e) $this->error($e);
        $cpu->messages = array();
        
        return $result;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Template error bridge
    ////////////////////////////////////////////////////////////
    function error($e) {
        tplerr($e,'<plain>');
        return false;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Strip PHP from templates
    ////////////////////////////////////////////////////////////
    function strip_php($tpl) {
        return str_replace(array('<?','<%'),array('<?php echo \'<\';?>?','<?php echo \'<\';?>%'),$tpl);
    }
    
    
    ////////////////////////////////////////////////////////////
    // Strip XML tag from templates (to stop errors with PHP short dilemiters)
    ////////////////////////////////////////////////////////////
    function strip_xml($tpl) {
        return str_replace('<?xml','<?php echo \'<\';?>?xml',$tpl);
    }
    
    
    ////////////////////////////////////////////////////////////
    // Compile header/footer as PHP
    ////////////////////////////////////////////////////////////
    function generate_globals() {
        $globals = $this->cpu('template','get_globals');
        foreach ($globals as $type => $global) {
            // Default variables
            $result = '<'.'?php'."\n";
            foreach ($global['vars'] as $var => $value) $result .= 'if (!isset($vars['.var_export($var,true).'])) $vars['.var_export($var,true).']='.var_export($value,true).';'."\n";
            $result .= '?'.'>'."\n";
            
            $tpl = $global['content'];
            if (!get_config('allowphp')) $tpl = $this->strip_php($tpl);
            $tpl = $this->strip_xml($tpl);
            
            // Plugin embed
            $tpl = preg_replace("/(\{\{)(plugin:)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$item,\"plugin_\\3\")",$tpl);
            $tpl = preg_replace("/(\{\{)(plugin:)([A-Za-z0-9_:]*)(\|)([A-Za-z0-9_:\.]*)(\}\})/e","tpl_var(\$item,\"plugin_\\3\",\"\\5\")",$tpl);
            
            // Simple vars replacement
            $tpl = str_replace(array('{{else}}','{{endif}}'),array('<'.'?php } else { ?'.'>','<'.'?php } ?'.'>'),$tpl);
            $tpl = preg_replace("/(\{\{)([A-Za-z0-9_]*)(\}\})/","<"."?php echo (isset(\$vars['\\2'])?\$vars['\\2']:'') ?".">",$tpl);
            $tpl = preg_replace("/(\{\{if:)([A-Za-z0-9_]*)(\}\})/","<"."?php if ( (isset(\$vars['\\2'])) && (\$vars['\\2']) ) { ?".">",$tpl);
            
            $globals[$type] = str_replace('http:///',get_config('baseurl'),$result.$tpl);
        }
        
        $this->cpu('cache','save_globals',$globals);
        
        return $globals;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Variables needed for generating a full page
    ////////////////////////////////////////////////////////////
    var $flags = array(
        'next_comic'=>false,
        'prev_comic'=>false,
        'first_comic'=>false,
        'last_comic'=>false,
        'next_news'=>false,
        'prev_news'=>false,
        'first_news'=>false,
        'last_news'=>false,
        'comic_news'=>false,
        'news_comic'=>false,
    );
    var $plugins = array(
        'comic_extra'=>array(),
        'news_extra'=>array(),
    );
    var $items = array(
        'comic'=>array(),
        'news'=>array(),
    );
    
    ////////////////////////////////////////////////////////////
    // Generate a page for cache
    // This function loads the required item data and plugins and calls other plugins
    ////////////////////////////////////////////////////////////
    function generate_page($fileid,$pageid,$cacheflag=false) {
        $page = $this->cpu('template','get',$fileid,$pageid);
        $result = array('vars'=>array(),'content'=>'');
        
        // Page properties
        $tpl = $page['content'];
        $type = ($page['type']=='C'?'comic':($page['type']=='N'?'news':'none'));
        
        // Check for flags
        foreach ($this->flags as $check => $bool) if (strpos(' '.$tpl,$check)) $this->flags[$check] = true;
        
        // Load needed template plugins
        $plugins = array();
        if ( ($type=='comic') || ($this->flags['news_comic']) ) $plugins = $this->cpu('plugin','search','comic','types');
        if ( ($type=='news') || ($this->flags['comic_news']) ) $plugins = array_merge($plugins,$this->cpu('plugin','search','news','types'));
        foreach ($plugins as $plugin) {
            $class = load_plugin($plugin['name']);
            if ( (in_array('comic',$plugin['types'])) && ($class->comic_trigger($tpl)) ) $this->plugins['comic_extra'][] = $class;
            if ( (in_array('news',$plugin['types'])) && ($class->news_trigger($tpl)) ) $this->plugins['news_extra'][] = $class;
        }
        
        // Load needed content
        $items = array();
        if ( ($type=='comic') || ($type=='news') ) {
            $sourceid = cvar(array_shift($page['source']));
            if (!$sourceid) $sourceid = '-0';
            switch ($page['function']) {
                case '1':
                    // Single item
                    $items = $this->get($type,'single',$sourceid);
                    break;
                
                case 'R':
                    // Sequential items
                    $toid = cvar(array_shift($page['source']));
                    if (!$toid) $toid = '-0';
                    $items = $this->get($type,'range',$sourceid,$toid,(isset($page['source'][0])?$page['source'][0]:null));
                    break;
                
                case 'B':
                    // Batch collection of items
                    $batch1 = cvar(array_shift($page['source']));
                    $batch2 = cvar(array_shift($page['source']));
                    $items = $this->get($type,'batch',$sourceid,$batch1,$batch2,(isset($page['source'][0])?$page['source'][0]:null));
                    
                    break;
                
                default:
                    // None
                    break;
            }
            
            // Item not found
            if (empty($items)) {
                header('HTTP/1.0 404 Not Found');
                show_templatepage('inc/error',1,array('s'=>array(0=>'',),'e'=>1,'c'=>'text/html',));
                exit;
            }
        }
        
        // First/Last links
        if ($this->flags['first_'.$type]) {
            if ($first = $this->cpu($type,'get','single','+0')) $this->items[$type]['first'] = $first;
        }
        if ($this->flags['last_'.$type]) {
            if ($last = $this->cpu($type,'get','single','-0')) $this->items[$type]['last'] = $last;
        }
        
        // Plugin modifiers
        foreach (array_keys($this->items) as $t) {
            if ($this->plugins[$t.'_extra']) {
                foreach ($this->items[$t] as $id => $item) {
                    foreach ($this->plugins[$t.'_extra'] as $k => $p) {
                        $method = $t.'_extra';
                        $this->items[$t][$id] = $this->plugins[$t.'_extra'][$k]->$method($item);
                    }
                }
            }
        }
        
        // Nested support
        if ($type=='comic') {
            foreach ($this->items['comic'] as $k => $v) {
                if ( ($this->flags['comic_news']) && ($v['news']) ) {
                    foreach ($v['news'] as $i => $n) {
                        $n = $this->get('news','single',$n);
                        $this->items['comic'][$k]['news'][$i] = array_pop($n);
                        if (!$this->items['comic'][$k]['news'][$i]) unset($this->items['comic'][$k]['news'][$i]);
                    }
                } else unset($this->items['comic']['news']);
            }
        }
        
        // Main item for sections supporting one item only
        reset($items);
        $main = &$items[key($items)];
        
        // Apply logic to template
        $tpl = $this->apply($tpl,$type,$page['function'],$items,$main);
        
        // Strip PHP
        if (!get_config('allowphp')) $tpl = $this->strip_php($tpl);
        $tpl = $this->strip_xml($tpl);
        
        $result['content'] = str_replace('http:///',get_config('baseurl'),$tpl);
        
        // Parse template variables
        foreach ($page['vars'] as $var => $value) $result['vars'][$var] = $this->parse($value,$type,$main);
        
        // Cache the result
        $this->addcache($result['content'],'tpl_'.$fileid,$pageid,$cacheflag,$result['vars']);
        
        return $result;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Save cache
    ////////////////////////////////////////////////////////////
    function addcache($content,$id,$subid=false,$flag=false,$vars=false) {
        $data = array(
            'id'=>$id,
            'subid'=>$subid,
            'flag'=>$flag,
            'content'=>$content,
            'vars'=>$vars,
        );
        
        foreach ($this->items as $type => $array) {
            $data[$type] = array();
            
            // Min & Max
            foreach ($array as $id => $item) {
                if ( ($id != 'first') && ($id != 'last') ) {
                    $data[$type]['min'] = (isset($data[$type]['min'])?min($data[$type]['min'],$id):$id);
                    $data[$type]['max'] = (isset($data[$type]['max'])?max($data[$type]['max'],$id):$id);
                }
            }
            
            // Check for first & last
            $data[$type]['first'] = $this->flags['first_'.$type];
            if ($this->items[$type][$data[$type]['min']]['isfirst']) $data[$type]['min'] = '+0';
            $data[$type]['last'] = $this->flags['last_'.$type];
            if ($this->items[$type][$data[$type]['max']]['islast']) $data[$type]['max'] = '-0';
        }
        
        $this->cpu('cache','add',$data);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Load item data
    ////////////////////////////////////////////////////////////
    function get($type,$function,$sourceid,$e1=null,$e2=null,$e3=null) {
        if ( ($function=='single') && (isset($this->items[$type][$sourceid])) ) return array($this->items[$type][$sourceid]['id']=>&$this->items[$type][$sourceid]);
        
        $rev = false;
        if ( ( ($function=='range') && ($sourceid>$e1) ) || ( ($function=='batch') && ($e1>0) ) ) $rev = true;
        
        // Retreive main items and index
        if (!$items = $this->cpu($type,'get',$function,$sourceid,$e1,$e2,$e3)) return array();
        if ($function=='single') $items = array($items['id']=>$items);
        $index = array();
        foreach ($items as $v) {
            $this->items[$type][$v['id']] = $v;
            $index[] = $v['id'];
        }
        
        // Fetch/apply prev then next links
        $link = 'prev';
        $id = min($index);
        $batch = -1;
        $list = $index;
        for ($x=0;$x<2;$x++) {
            if ($this->flags[$link.'_'.$type]) {
                $prev = $this->cpu($type,'get','batch',$id,$batch,0,$e3);
                foreach ($prev as $v) {
                    if ($v['id'] != $id) {
                        $this->items[$type][$v['id']] = $v;
                        $this->items[$type][$id][$link] = &$this->items[$type][$v['id']];
                    }
                }
                
                $last = false;
                foreach ($list as $k) {
                    if ($last) $this->items[$type][$k][$link] = &$this->items[$type][$last];
                    $last = $k;
                }
            }
            
            // Setup next loop as next
            $link = 'next';
            $id = max($index);
            $batch = 1;
            $list = array_reverse($index);
        }
        
        // Format result list
        $return = array();
        foreach ($index as $k) $return[$this->items[$type][$k]['id']] = &$this->items[$type][$k];
        return $return;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Apply template logic
    ////////////////////////////////////////////////////////////
    function apply($tpl,$type='none',$function=false,$items=array(),$main=array()) {
        if ( ($function == 'R') || ($function == 'B') ) {
            $d1 = '{{foreach:'.$type.'}}';
            $d2 = '{{endeach:'.$type.'}}';
            if ( (strpos(" ".$tpl,$d1)) && (strpos($tpl,$d2)) ) {
                $pre = $this->apply(substr($tpl,0,strpos($tpl,$d1)),$type,1,array($main),$main);
                $post = $this->apply(substr($tpl,strpos($tpl,$d2)+strlen($d2)),$type,1,array($main),$main);
                $foreach = substr($tpl,strpos($tpl,$d1)+strlen($d1),strpos($tpl,$d2)-(strpos($tpl,$d1)+strlen($d1)));
                
                $tpl = '';
                foreach ($items as $item) {
                    $tpl .= $this->apply($foreach,$type,1,array($item),$item);
                }
                return $pre.$tpl.$post;
            }
        } elseif (is_array($main)) {
            $match = false;
            foreach ($main as $attr => $values) {
                if (is_array($values)) {
                    // Quick hack for reversing arrays
                    for ($rev=0;$rev<2;$rev++) {
                        if ($rev) {
                            $d1 = '{{foreach:'.$type.'_'.$attr.':reverse}}';
                        } else $d1 = '{{foreach:'.$type.'_'.$attr.'}}';
                        $d2 = '{{endeach:'.$type.'_'.$attr.'}}';
                        if ( (strpos(" ".$tpl,$d1)) && (strpos($tpl,$d2)) ) {
                            $match = true;
                            
                            $pre = $this->apply(substr($tpl,0,strpos($tpl,$d1)),$type,1,array($main),$main);
                            $post = $this->apply(substr($tpl,strpos($tpl,$d2)+strlen($d2)),$type,1,array($main),$main);
                            $foreach = substr($tpl,strpos($tpl,$d1)+strlen($d1),strpos($tpl,$d2)-(strpos($tpl,$d1)+strlen($d1)));
                            
                            $tpl = '';
                            if ($rev) $values = array_reverse($values);
                            foreach ($values as $value) {
                                $value['tag']=$main['tag'];
                                $tpl .= $this->apply($foreach,$attr,1,array($value),$value);
                            }
                            $tpl = $pre.$tpl.$post;
                        }
                    }
                }
            }
            if ($match) return $this->apply($tpl,$type,1,array($main),$main);
        }
        return $this->parse($tpl,$type,$main);
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Parse template with given variables
    ////////////////////////////////////////////////////////////
    function parse($tpl,$type='none',$item=array()) {
        // Plugin embed
        $tpl = preg_replace("/(\{\{)(plugin:)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$item,\"plugin_\\3\")",$tpl);
        $tpl = preg_replace("/(\{\{)(plugin:)([A-Za-z0-9_:]*)(\|)([A-Za-z0-9_:\.]*)(\}\})/e","tpl_var(\$item,\"plugin_\\3\",\"\\5\")",$tpl);
        
        // IFs
        if (strpos(' '.$tpl,'{{if:')) {
            $tpl = explode('{{if:',$tpl);
            $before = array_shift($tpl);
            foreach ($tpl as $k => $t) {
                $tag = substr($t,0,strpos($t,'}}'));
                $case = substr($t,strpos($t,'}}')+2,strpos($t,'{{endif}}')-(strpos($t,'}}')+2));
                $after = substr($t,strpos($t,'{{endif}}')+9);
                
                $v = &$item;
                if (substr($tag,0,5)=='prev_') {
                    $tag = substr($tag,5);
                    $v = &$item['prev'];
                }
                if (substr($tag,0,5)=='next_') {
                    $tag = substr($tag,5);
                    $v = &$item['next'];
                }
                $if = tpl_var($v,$tag,'bool');
                if (strpos(' '.$case,'{{else}}')) {
                    if ($if) {
                        $answer = substr($case,0,strpos($case,'{{else}}'));
                    } else {
                        $answer = substr($case,strpos($case,'{{else}}')+8);
                    }
                } elseif ($if) {
                    $answer = $case;
                } else {
                    $answer ='';
                }
                $tpl[$k] = $answer . $after;
            }
            $tpl = $before . implode($tpl);
        }
        
        // General variable replacement
        $tpl = preg_replace("/(\{\{)(" . $type . ")([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$item,\"\\2\\3\")",$tpl);
        if (isset($item['prev'])) $tpl = preg_replace("/(\{\{)(prev_)(" . $type . "_)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$item['prev'],\"\\3\\4\")",$tpl);
        if (isset($item['next'])) $tpl = preg_replace("/(\{\{)(next_)(" . $type . "_)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$item['next'],\"\\3\\4\")",$tpl);
        
        // First/Last variables
        if (isset($this->items[$type]['first'])) $tpl = preg_replace("/(\{\{)(first_)(" . $type . "_)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$this->items[\$type]['first'],\"\\3\\4\")",$tpl);
        if (isset($this->items[$type]['last'])) $tpl = preg_replace("/(\{\{)(last_)(" . $type . "_)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$this->items[\$type]['last'],\"\\3\\4\")",$tpl);
        
        // Special variables
        $cache = array(
            'timestamp'=>date('U'),
        );
        $tpl = preg_replace("/(\{\{)(cache_)([A-Za-z0-9_:]*)(\}\})/e","tpl_var(\$cache,\"\\2\\3\")",$tpl);
        
        return str_replace('http:///',get_config('baseurl'),$tpl);
    }
    
}



////////////////////////////////////////////////////////////
// Create template variable replacement
////////////////////////////////////////////////////////////
function tpl_var($values,$tag,$type=false) {
    if (!strpos(" ".$tag,'_')) {
        $tag = array('none',false);
    } else {
        $tag = explode('_',$tag);
    }
    
    // Type still in tag
    $t = &$tag[(count($tag)-1)];
    if ($p = strpos(" ".$t,':')) {
        $p--;
        $type = substr($t,($p+1));
        $t = substr($t,0,$p);
        if ($type =='count') {  //@@@ERIK
            if (isset($values[$tag[1]])) {
                if (isset($values[$tag[1]][$tag[2]])) return (count($values[$tag[1]][$tag[2]]));
                else return(count($values[$tag[1]]));
            }
        }
    }
    
    $return = false;
    switch ($tag[0]) {
        case 'plugin':
            $plugin = $tag[1];
            if (!$class = load_plugin($plugin)) return tplerr('pluginbad',$plugin);
            if (!method_exists($class,'plugintpl')) return tplerr('pluginnomethod',$plugin . '->plugintpl');
            $return = $class->plugintpl($type,$values);
            if (!$type) $type = 'html';
            break;
        case 'plugincache':
            $return = db_return_plugincache($tag[1],$type);
            if (!$type) $type = 'html';
            break;
        case 'pluginrebuild':
            $plugin = load_plugin($tag[1]);
            $return = $plugin->pluginrebuild($type);
            if (!$type) $type = 'html';
            break;
        
        case '': case 'none':
            $return = $values;
            break;
        
        default:
            if (isset($values[$tag[1]])) {
                if ( (isset($tag[2])) && ($tag[2]) ) {
                    if (isset($values[$tag[1]][$tag[2]])) $return = $values[$tag[1]][$tag[2]];
                } else $return = $values[$tag[1]];
            } elseif ( ($type!='timestamp') && ($timestamp = tpl_var($values,$tag[0].'_timestamp','timestamp')) ) {
                switch ($tag[1]) {
                    case 'year': $return = date('Y',$timestamp); break;
                    case 'month': $return = date('m',$timestamp); break;
                    case 'weekday': $return = date('w',$timestamp); break;
                    case 'day': $return = date('d',$timestamp); break;
                    case 'hour': $return = date('H',$timestamp); break;
                    case 'minute': $return = date('i',$timestamp); break;
                }
                if ( (!$type) && ($return) ) $type = 'unpad';
            }
            break;
    }
    
    
    // Logic statements
    switch (substr($type,0,3)) {
        case '!==': return ((is_array($return)?(in_array(strtolower(substr($type,3)),array_map('strtolower',$return))):($return==substr($type,3)))?0:1); break;  //@@@ERIK
    }
    switch (substr($type,0,2)) {
        case '==': return ((is_array($return)?(in_array(strtolower(substr($type,2)),array_map('strtolower',$return))):($return==substr($type,2)))?1:0); break;	//@@@ERIK
        case '!=': return (strtolower($return)==strtolower(substr($type,2))?0:1); break;
        case '>=': return ($return>=substr($type,2)?1:0); break;
        case '<=': return ($return<=substr($type,2)?1:0); break;
    }
    switch (substr($type,0,1)) {
        case '!': return ($return?0:1); break;
        case '=': return (strtolower($return)==strtolower(substr($type,1))?1:0); break;
        case '>': return ($return>substr($type,1)?1:0); break;
        case '<': return ($return<substr($type,1)?1:0); break;
    }
    
    
    // Variable operators
    switch ($type) {
        // Text handlers
        case 'plain': return tpl_handle_plain($return,$tag[1]); break;
        case 'rich': return tpl_handle_rich($return,$tag[1]); break;
        case 'full': return tpl_handle_full($return,$tag[1]); break;
        case 'html': return tpl_handle_html($return,$tag[1]); break;
        case 'md5': return md5($return); break;
        // Code handlers
        case 'var': return var_export($return,true); break;
        
        // XML text handlers
        case 'plain:xml': case 'xml': return plaintext(tpl_handle_plain($return,$tag[1])); break;
        case 'rich:xml': return plaintext(tpl_handle_rich($return,$tag[1])); break;
        case 'full:xml': return plaintext(tpl_handle_full($return,$tag[1])); break;
        case 'html:xml': return plaintext(tpl_handle_html($return,$tag[1])); break;
        
        // Date handlers
        case 'pad': return $return; break;
        case 'unpad': return tpl_handle_unpad($return,$tag[1]); break;
        case 'name': return tpl_handle_name($return,$tag[1]); break;
        case 'shortname': return tpl_handle_shortname($return,$tag[1]); break;
        case 'rfc': return date('r',$return); break;
        case 'short_date': case 'short_both': case 'long_both': case 'cur_time': case 'time':
            if ($tag[1]!='timestamp') return false;
            return date(get_lang('date',$type),$return);
            break;
        
        // Special internal handlers
        case 'bool': return ($return?1:0); break;
        case 'timestamp': return $return * 1; break;
        
        // Default (plaintext)
        default: return tpl_handle_plain($return,$tag[1]); break;
    }
}
// Text handlers
function tpl_handle_plain($text) {
    if (!is_string($text)) $text = ''.$text;
    $text = htmlspecialchars($text,ENT_QUOTES);
    $text = preg_replace('/(&amp;)([a-zA-Z]{2,6})(;)/', '&\\2;',$text);
    $text = preg_replace('/(&amp;\#)([0-9]{1,3})(;)/', '&#\\2;',$text);
    $text = str_replace(array("\n\n","\n"),array('</p><p>','<br />'),$text);
    return $text;
}
function tpl_handle_full($text) {
    $text = str_replace(array("\n\n","\n"),array('</p><p>','<br />'),$text);
    return $text;
}
function tpl_handle_rich($text) {
    $nbbc = load_class('nbbc');
    return $nbbc->richtext($text);
}
function tpl_handle_html($text) {
    return $text;
}
// Number handlers
function tpl_handle_unpad($v,$type) {
    if ($type=='year') return substr($v,-2);
    while ( strlen($v)>1 && substr($v,0,1)=='0') $v = substr($v,1);
    if ( ($type=='month') || ($type=='weekday') || ($type=='day') ) return $v;
    return false;
}
function tpl_handle_name($v,$type) {
    if ($type=='weekday') return get_lang('date','week'.$v);
    if ($type=='month') return get_lang('date','month'.tpl_handle_unpad($v,$type));
    return false;
}
function tpl_handle_shortname($v,$type) {
    if ( ($type=='weekday') || ($type=='month')) return substr(tpl_handle_name($v,$type),0,3);
    return false;
}
