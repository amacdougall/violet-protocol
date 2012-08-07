<?php

class admin_global {
    var $section = 'main';
    var $page = false;
    var $navigation = array();
    var $navextra = '';
    var $javascript = '';
    var $content = '';
    var $templates = array();
    
    
    
    
    
    
    
    
    function addline($string) {$this->content .= '    ' . $string . "\n";}
    function addjavascript($js) {$this->javascript .= $js . "\n";}
    
    
    
    function display() {
        if ($_GET['type']=='inline') {
            @header('Content-Type: text/html; charset=UTF-8');
            
            $auth = load_cpu('auth');
            // Javascript readable stuffs
            $htmlresult = '<html><head><title>' . $this->section . '::' . get_lang('title',$this->section . ($this->page=='main'?'':$this->page)) . '</title></head><body><!--ComicCMS_Body//--><div id="pagetop">' . ((($this->section!='special')&&($auth->get('user')))?'<div id="userbar"><a href="?p=special:editself">' . $auth->get('user','name') . '</a></div>':'');
            
            // Damn, this navbar stuff sure is complicated
            $section = $this->navigation;
            if ($this->section == 'special') {
                $loc = array();
                $nav = array();
            } elseif ( ($this->page) && (isset($section[$this->page])) ) {
                $page = $section[$this->page];
                if (strpos($page,'|')) {
                    list($page,$pos) = explode('|',$page);
                    $find = $page;
                } else {
                    $pos = $page;
                    $find = $this->page;
                }
                if ( ($pos == 1) || (!$pos) || ($pos==$this->page) ) {
                    $loc = array($this->section,$this->page);
                    $nav = array();
                    foreach ($section as $key => $join) {
                        if ( ($join == $find) || (substr($join,0,strlen($find)+1) == $find . '|') ) $nav[] = $key;
                    }
                    if (empty($nav)) {
                        foreach ($section as $key => $value) {
                            if ($value == 1) $nav[] = $key;
                        }
                    }
                } else {
                    $loc = array($this->section,$pos,$this->page);
                    $nav = array();
                    foreach ($section as $key => $join) {
                        if ( ($join == $page) || (substr($join,0,strlen($page)+1) == $page . '|') ) $nav[] = $key;
                    }
                    if (empty($nav)) {
                        $nav = array();
                        foreach ($section as $key => $value) {
                            if ($value == 1) $nav[] = $key;
                        }
                    }
                }
            } else {
                $loc = array($this->section);
                $nav = array();
                foreach ($section as $key => $value) {
                    if ($value == 1) $nav[] = $key;
                }
            }
            
            // Still going....
            if ($this->section != 'special') {
                $htmlresult .= "\n" . '<div id="innernav"><div id="locationstore"><a href="?" id="innerhomelink">Home</a>';
                foreach ($loc as $key => $link) {
                    if ( (isset($loc[($key+1)])) && ($this->section != 'main') )
                    $htmlresult .= '&gt; <a href="?p=' . ($link==$this->section?$link:$this->section . ':' . $link . ($this->navextra?'&amp;' . $this->navextra:'')) . '">' . get_lang('title',($link==$this->section?$link:$this->section . $link)) . '</a>';
                }
                $htmlresult .= '</div><div id="innersectionnav">';
                if ($nav) {
                    $first = true;
                    foreach ($nav as $link) {
                        $htmlresult .=  ($first?'':'| ') . '<a href="?p=' . $loc[0] . ($link=='main'?'':':' . $link) . ($this->navextra?'&amp;' . $this->navextra:'') . '"' . ($link==$this->page?' class="selected"':'') . '>' . get_lang('title',$loc[0] . ($link=='main'?'':$link)) . '</a> ';
                        $first = false;
                    }
                } else {
                    $htmlresult .= '<a href="#" style="visibility:hidden;">_</a>';
                }
                $htmlresult .= '</div>';
                $htmlresult .= '</div>';
            }
            $htmlresult .= '<p class="clear"></p></div><!--ComicCMS_Page';
            if ($desc = get_lang('intro',$this->section . ($this->page=='main'?'':$this->page),false,false,true)) {
                $htmlresult .= "\n" . '<p>' . (is_array($desc)?implode('</p><p>',$desc):$desc) . '</p>';
            }
            $htmlresult .= "\n" . $this->content . "\n" . (get_config('adminmode')?'<span class="mode">' . get_lang('admin','mode_' . get_config('adminmode')) . '</span></h1>' . "\n":'') . '__ComicCMS javascript__' . "\n" . $this->javascript . 'ComicCMS javascript//--></body></html>';
            
            // Are "templates" even used anymore?
            foreach($this->templates as $from => $to) {
                $htmlresult = str_replace('{{' . $from . '}}',$to,$htmlresult);
            }
            
            // Some early translation ideas, I wonder if this is gonna stay in much longer..
            if (get_config('adminmode')=='translate') {
                $match = '(\[\[)([a-zA-Z\_]*)(\ \-\ )([a-zA-Z\_]*)(\]\])([a-zA-Z0-9\!\&\;\ _\-\+\*\:\{\}\=\<\>\"\/\|\'\[\]\(\)\,\.\#]*)(\^)';
                $tagsplit = explode('<',$htmlresult);
                foreach ($tagsplit as $key => $section) {
                    $workon = explode('>',$section);
                    if ($workon[0]) {
                        if (substr($workon[0],-1,1) == '/') {
                            $endwith = ' /';
                            $workon[0] = substr($workon[0],0,(strlen($workon[0])-2));
                        } else $endwith = '';
                        $vars = false;
                        $workon[0] = preg_replace("/$match/e","((\$vars=\"\\2 - \\4\")?'\\6':'\\6');",$workon[0]);
                        if ($vars) $workon[0] .= ' title="' . $vars .'"';
                        $workon[0] .= $endwith;
                    }
                    if (isset($workon[1])) $workon[1] = preg_replace("/$match/","<abbr title=\"\\2 - \\4\">\\6</abbr>",$workon[1]);
                    $tagsplit[$key] = ($workon[0]?$workon[0] . '>':'') . (isset($workon[1])?$workon[1]:'');
                }
                $htmlresult = implode('<',$tagsplit);
            }
        } elseif ($_GET['type']=='ajax') {
            $htmlresult = '<' . '?xml version="1.0"?' . '>' . "\n" . '<root>' . "\n" . $this->content . "\n" . '</root>';
        } elseif ($_GET['type']=='json') {
            header('content-type:text/json');
            $htmlresult = trim($this->content);
        } else {
            //$this->adderror(get_lang('admin','displaytypeunknown'));
            $htmlresult = $this->content;
        }
        
        // Create current URL
        unset($_GET['type']);
        $url = '?';
        foreach ($_GET as $key => $value) $url .= urlencode($key).'='.urlencode($value).'&amp;';
        
        // Apply easylinks
        $html = str_replace('="&:',$url,$htmlresult);
        $html = str_replace('http:///',get_config('baseurl'),$html);
        
        // All done
        echo $html;
    }
}
?>