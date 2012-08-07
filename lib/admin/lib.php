<?php
$GLOBALS['admin'] = array(
    'section'=>'main',
    'page'=>'main',
    'navigation'=>array(),
    'navextra'=>'',
    'html'=>'',
    'javascript'=>'',
);


function setvar(&$var,$look,$index=false) {
    if ($var) return $var;
    if ($index) {
        switch ($look) {
            case 'g': $var = (isset($_GET[$index])?$_GET[$index]:false); break;
            case 'p': $var = (isset($_POST[$index])?$_POST[$index]:false); break;
            case 'f': $var = (isset($_FILES[$index])?$_FILES[$index]:false); break;
            default: $var = false; break;
        }
    } else {
        switch ($look) {
            case 'g': $var = $_GET; break;
            case 'p': $var = $_POST; break;
            case 'f': $var = $_FILES; break;
            default: $var = false; break;
        }
    }
    return $var;
}


function scrub_utf8($input) {
    //First, deal with preserving the special windows-only characters for those that like to cut & past from MS Word
    $badwordchars=array(
        "\xe2\x80\x98", // left single quote
        "\xe2\x80\x99", // right single quote
        "\xe2\x80\x9c", // left double quote
        "\xe2\x80\x9d", // right double quote
        "\xe2\x80\x94", // em dash
        "\xe2\x80\xa6" // elipses
    );
    $fixedwordchars=array(
        "'",
        "'",
        '"',
        '"',
        '--',
       '...'
    );
    $res=str_replace($badwordchars,$fixedwordchars,$input);
    //Now, destroy anything left.
    $res= preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $res);
    return $res;
}


class admin_editlist {
    var $items = array();
    
    function add($v) {
        $item = '';
        if (isset($v['id'])) $item .= '<span class="identify">'.$v['id'].'</span>';
        if (isset($v['delete'])) $item .= '<a href="'.$v['delete'].'" class="delete" title="'.$v['delete_title'].'">X</a>';
        $item .= '<a href="'.$v['url'].'">'.$v['title'].'</a>';
        $this->items[] = $item;
    }
    
    function render($id=false) {
        return '<ul class="editlist"'.($id?' id="'.$id.'"':'').'><li>'.implode('</li><li>',$this->items).'</li></ul>';
    }
}






class timestamp_input extends admin_lib {
    var $type = 'timestamp';
    var $min = 0;
    var $max = 0;
    var $value = false;
    var $draft = false;
    
    function allowdraft() {$this->draft = true;}
    function lowerlimit($value) {$this->min = $value;}
    function upperlimit($value) {$this->max = $value;}
    function setvalue($value) {$this->value = $value;}
    
    function render($value,$id) {
        if ($this->value !== false) $value = $this->value;
        
        $draft = 0;
        if ($this->draft) {
            $draft = 1;
            if ($value == 9999999999) {
                $draft = 2;
                $value = date('U');
            }
        }
        
        
        $html = '  <input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
        $html .= '  <table id="timestamp_table">';
        $html .= '    <tr><th>'.get_lang('date','year').'</th><th>'.get_lang('date','month').'</th><th>'.get_lang('date','day').'</th><th>'.get_lang('date','hour').'</th><th>'.get_lang('date','minute').'</th></tr>';
        $html .= '    <tr id="timestamp_up"><td id="year_up"></td><td id="month_up"></td><td id="day_up"></td><td id="hour_up"></td><td id="minute_up"></td></tr>';
        $html .= '    <tr id="timestamp_time"><td id="timestamp_year"></td><td id="timestamp_month"></td><td id="timestamp_day"></td><td id="timestamp_hour"></td><td id="timestamp_minute"></td></tr>';
        $html .= '    <tr id="timestamp_down"><td id="year_down"></td><td id="month_down"></td><td id="day_down"></td><td id="hour_down"></td><td id="minute_down"></td></tr>';
        $html .= '    <tr id="timestamp_drafton"><td colspan="5">'.get_lang('admin','timestamp_drafton').'</td></tr>';
        $html .= '    <tr id="timestamp_draft"><td colspan="5">'.get_lang('admin','timestamp_draft').'</td></tr>';
        $html .= '    <tr id="timestamp_nodraft"><td colspan="5">'.get_lang('admin','timestamp_nodraft').'</td></tr>';
        $html .= '  </table>';
        
        $html .= '  <div style="clear:left;"></div>';
        
        $js = 'set_timestamp($(\''.$id.'\'),'.$this->max.','.$this->min.','.$value.','.$draft.');'."\n";
        
        return array('html'=>$html,'js'=>$js);
    }
    
    
    
    function read($value) {
        if ( ($this->draft) && ($value == 9999999999) ) return $value;
        
        if ($value < $this->min) {
            echo $value.'<br />'.$this->min.'<br />';
            $this->adderror(get_lang('admin','timestamp_min'));
            return array('result'=>'bad');
        }
        
        if ( ($this->max > 0) && ($value > $this->max) ) {
            $this->adderror(get_lang('admin','timestamp_max'));
            return array('result'=>'bad');
        }
        
        $value = floor($value/60)*60;
        return $value;
    }
}





class select_input extends admin_lib {
    var $title = false;
    var $type = 'select';
    var $items = array();
    var $selected = false;
    
    
    function add($value,$title=false,$selected=false) {
        $this->items[$value] = ($title?$title:$value);
        if ($selected) $this->selected = $value;
    }
    
    function render($value,$id) {
        if ( ($value) && (@isset($this->items[$value])) ) $this->selected = $value;
        
        $html = '<select name="'.$id.'">';
        foreach ($this->items as $value => $title) {
            $html.= '<option value="'.$value.'"'.($this->selected == $value?' selected="selected"':'').'>'.$title.'</option>';
        }
        return $html.'</select>';
    }
    
    function read($value) {
        if (!@isset($this->items[$value])) {
            $this->adderror(get_lang('admin','select_hax'));
            return array('result'=>'bad');
        }
        return $value;
    }
}



class item_list extends select_input {
    function item_list($type) {
        $cpu = load_cpu($type);
        foreach ($cpu->get() as $id => $item) {
            $this->add($id,$item['name']);
        }
    }
}




class tag_input {
    var $type = 'tag';
    
    function render($tags=false) {
        if (!is_array($tags)) {
            $tags = explode(',',$tags);
        }
        $html = '  <input type="hidden" name="tags" id="tags" value="' . implode(',',$tags) . '" />';
        $html .= '  <div id="tagset">';
        $html .= '      <div><a href="javascript:addtag();">+ Add Tag +</a></div>';
        foreach ($tags as $tag) {
            if ($tag) {
                $html .= '      <div id="tag_' . $tag . '"><span>' . $tag . '</span><a href="javascript:untag(\'' . $tag . '\');">x</a></div>';
            }
        }
        $html .= '  </div><p style="clear:left;"></p>';
        return $html;
    }
    
    function read($value) {
        return explode(',',$value);
    }
}





class admin_lib {
    var $section = false;
    var $subsection = false;
    var $lang = false;
    
    function switchboard($page) {
        if (isset($_POST['save'])) {
            if ($this->hasperm($this->section,($this->subsection?$this->subsection.':'.$page:$page))) {
                $method = 'p_'.$page;
            } else {
                unset($_POST['save']);
                $this->switchboard($page);
                return false;
            }
        } else {
            $method = 'd_'.$page;
        }
        
        if (method_exists($this,$method)) {
            $this->$method();
        } else {
            $this->adderror(get_lang('admin','badpage'));
            $this->d_main();
        }
    }
    
    
    function d_main() {
        $this->newpage('main');
        $this->error('nomain');
    }
    
    
    
    
    
    function admin_lib() {
        $auth = load_cpu('auth');
        
        // Initiate admin page
        if (!isset($GLOBALS['adminpage'])) {
            import('.../lib/admin/class.php');
            $GLOBALS['adminpage'] = new admin_global;
            
            // Check if it's install time
            $userdb = load_db('user');
            if ($userdb->firstid()==0) {
                load_admin('special','install');
            
            // Determine login status
            } else {
                if (isset($_POST['login'])) {
                    // Logging in....
                    if ( (!isset($_POST['username'])) || (!$_POST['username']) ) {
                        $this->adderror(get_lang('special','usernamemissing'));
                        return load_admin('special','login');
                    }
                    if ( (!isset($_POST['userpass'])) || (!$_POST['userpass']) ) {
                        $this->adderror(get_lang('special','userpassmissing'));
                        return load_admin('special','login');
                    }
                    if ($err = $auth->login($_POST['username'],$_POST['userpass'])) {
                        $this->adderror(get_lang('special',$err));
                        return load_admin('special','login');
                    }
                } elseif ($_GET['type']=='logout') {
                    // Logging out...
                    $auth->logout();
                    header('Location: ' . get_config('baseurl') . 'admin/');
                    die('Location: ' . get_config('baseurl') . 'admin/');
                } elseif ($auth->get('loggedin')) {
                    // Already logged in
                    
                } else load_admin('special','login');
            }
        }
        
        if ( ($this->section!='special') && (!$auth->get('loggedin')) ) $this->end();
        $GLOBALS['adminpage']->user = $auth->get('user');
    }
    
    
    function end() {
        if ($this->cpu('auth','get','user','id')) {
            if ($this->cpu('auth','get','usergroup','permissions','_checkupdate')) {
                $this->addjavascript("loggedinload(".($this->cpu('auth','get','usergroup','permissions','_superuser')?'true':'false').",true);");
            } else $this->addjavascript("loggedinload(".($this->cpu('auth','get','usergroup','permissions','_superuser')?'true':'false').");");
            if ($this->cpu('auth','is_super')) {
                $this->addjavascript("issuperuser();");
            } else $this->addjavascript("isnotsuperuser();");
        }
        $GLOBALS['adminpage']->display();
        exit;
    }
    
    
    
    
    
    // Shortcuts, lots of 'em
    function user($attr=false) {return $GLOBALS['adminpage']->user($attr);}
    function usergroup($attr=false) {return $GLOBALS['adminpage']->usergroup($attr);}
    
    function addline($text) {$GLOBALS['adminpage']->addline($text);}
    function addjavascript($text) {$GLOBALS['adminpage']->addjavascript($text);}
    
    
    
    
    
    
    
    
    
    function hasperm($section,$page='main') {
        if ( ($section == 'main') || ($section == 'special') ) return true;
        
        $usergroup = $this->cpu('auth','get','usergroup');
        
        // Super user
        $super = array('plugin','template','page','config','user','usergroup');
        if ( (in_array($section,$super)) && (!$this->cpu('auth','is_super')) ) return false;
        if ($page=='_checksuper') return true;
        
        // Permission
        $p = $section . ($page=='main'?'':$page);
        return ( (isset($usergroup['permissions'][$p])) && ($usergroup['permissions'][$p]) );
    }
    
    
    
    function newpage($page=false,$navextra=false) {
        if ( ($GLOBALS['adminpage']->section == $this->section) && ($GLOBALS['adminpage']->page == $page) ) {
            // Permissions already checked
            $GLOBALS['adminpage']->navextra = $navextra;
            return true;
        }
        $GLOBALS['adminpage']->section = $this->section;
        $GLOBALS['adminpage']->page = $page;
        $GLOBALS['adminpage']->navextra = $navextra;
        $GLOBALS['adminpage']->navigation = $this->navigation;
        
        if ( ($this->section == 'main') || ($this->section == 'special') ) return true;
        
        
        
        
        // Super user
        if (!$this->hasperm($this->section,'_checksuper')) {
            $_POST['section'] = $this->section;
            $_POST['page'] = $page;
            load_admin('special','login_super');
            $this->end();
        }
        
        
        $user = $this->cpu('auth','get','user');
        $usergroup = $this->cpu('auth','get','usergroup');
        
        if (!isset($usergroup['permissions'][$this->section . ($page=='main'?'':$page)])) {
            $this->adddebug('<span style="font-weight:bold;">' . $this->section . $page . '</span> Permission regression');
            
            // Fallback permissions
            if (isset($this->navigation[$page])) {
                $revert = $this->navigation[$page];
                $revert = (strpos($revert,'|')?substr($revert,strpos($revert,'|')+1):$revert);
                if ( ($revert) && ($revert != '1') && ($this->hasperm($this->section,$revert)) ) return true;
            }
        }
        if ($this->hasperm($this->section,$page)) {
            return true;
        } else {
            // Fallback page
            $this->adderror('<span style="font-weight:bold;">' . $this->section . $page . '</span> ' . get_lang('admin','permbad'));
            if ($page) {
                if (isset($this->navigation[$page])) {
                    $revert = $this->navigation[$page];
                    $revert = (strpos($revert,'|')?substr($revert,0,strpos($revert,'|')):$revert);
                    if ( ($revert) && ($revert != '1') && ($this->hasperm($this->section,$revert)) ) {
                        load_admin($this->section,$revert);
                        $this->end();
                    }
                }
                if ($this->hasperm($this->section)) {
                    load_admin($this->section,'main');
                    $this->end();
                }
            }
            load_admin('main','main');
            $this->end();
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function display() {$GLOBALS['adminpage']->display();}
    
    function error($lang,$extra=false) {$this->adderror(get_lang(($this->lang?$this->lang:$this->section),$lang,$extra));}
    function warning($lang,$extra=false) {$this->addwarning(get_lang(($this->lang?$this->lang:$this->section),$lang,$extra));}
    function good($lang,$extra=false) {$this->addgoodmessage(get_lang(($this->lang?$this->lang:$this->section),$lang,$extra));}
    
    
    
    
    
    function add_arrlist($array,$lang='general',$padding='') {
        $this->addline($padding . '<ul>');
        foreach ($array as $lcode => $value) {
            if (is_array($value)) {
                $this->addline($padding . '  <li><span class="heading">' . get_lang($lang,$lcode) . '</span>');
                $this->add_arrlist($value,$lang,$padding . '    ');
                $this->addline($padding . '  </li>');
            } else {
                $this->addline($padding . '  <li><a href="' . $value . '">' . get_lang($lang,$lcode) . '</a></li>');
            }
        }
        $this->addline($padding . '</ul>');
    }
    function addgoodmessage($string) {$this->addline('<span class="goodmsg">' . $string . '</span>');}
    function adderror($string) {$this->addline('<div class="errmsg"><h3>' . get_lang('admin','error') . '!</h3><p>' . $string . '</p></div>');}
    function addwarning($string) {$this->addline('<div class="warning"><h3>' . get_lang('admin','warning') . ':</h3><p>' . $string . '</p></div>');}
    function adddebug($string) {
        if (get_config('adminmode')=='debug') $this->addline('<span class="debug">' . $string . '</span>');
    }
    
    var $silence=false;
    function silence() {$this->silence = true;}
    function speak() {$this->silence = false;}
    // CPU bridge
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
        $messages = $cpu->messages;
        if (isset($messages['error'])) {
            foreach ($messages['error'] as $string) $this->adderror($string);
        }
        if (isset($messages['warning'])) {
            foreach ($messages['warning'] as $string) $this->addwarning($string);
        }
        
        if (!$this->silence) {
            if (isset($messages['good'])) {
                foreach ($messages['good'] as $string) $this->addgoodmessage($string);
            }
        }
        
        if (isset($messages['debug'])) {
            foreach ($messages['debug'] as $string) $this->adddebug($string);
        }
        
        $cpu->messages = array();
        
        return $result;
    }
    
    
    
    
    
    function v_batch($type,$id,$batch1=false,$batch2=false,$batch3=false) {
        if ( ($batch1) || ($batch2) || ($batch3) ) {
            $items = $this->cpu($type,'get','batch',$id,$batch1,$batch2,$batch3); // BUG: returns bad when base ID is first item :O
            if ( (!$items) || (!isset($items[$id])) ) return false;
            return $items;
        } else {
            return $this->cpu($type,'get',$id);
        }
    }
    
    
    function url() {
        return '?p=' . $this->section . ':' . $GLOBALS['adminpage']->page . ($GLOBALS['adminpage']->navextra?'&amp;' . $GLOBALS['adminpage']->navextra:'');
    }
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Refresh user's cache of comic images
    ////////////////////////////////////////////////////////////
    function reload_comicimg($comic) {
        if (function_exists('gd_info')) $this->addjavascript('reload_img("http:///admin/img/thumb/'.$comic['id'].'.jpg");');
        $this->addjavascript('reload_img("http:///img/comic/'.$comic['id'].'.'.$comic['ext'].'");');
    }
    
    
    
    
    
    function showcomic($comic) {
        $comic = plaintext($comic);
        switch(strtolower($comic['ext']))  {
            case 'jpg': case 'jpeg': case 'png': case 'gif':
                return '<img src="'.$comic['src'].'" alt="" />';
                break;
            case 'swf':
                return '<object type="application/x-shockwave-flash"
  data="'.$comic['src'].'" width="'.$comic['width'].'" height="'.$comic['height'].'">
  <param name="loop" value="true" />
  <param name="menu" value="false" />
</object>';
                break;
            default:
                return get_lang('admin','comicnodisplay');
                break;
        }
    }
    
    
    
    
    function genform($contents,$values,$extra=array()) {
        $extra['method'] = (isset($extra['method'])?$extra['method']:'post');
        $extra['action'] = (isset($extra['action'])?$extra['action']:$this->url());
        
        $this->addline('<div class="formtop">');
        if (isset($extra['img'])) {
            $this->addline('    <img src="http:///admin/img/crystal/' . $extra['img'] . '.png" alt="" />');
            unset($extra['img']);
        }
        $this->addline('    <h2>' . (isset($extra['title'])?$extra['title']:get_lang('title',$this->section . $GLOBALS['adminpage']->page)) . '</h2>');
        $this->addline('</div>');
        
        $html = false;
        if (isset($extra['html'])) {
            $html = $extra['html'];
            unset($extra['html']);
        }
        
        $form = '<form';
        foreach ($extra as $key => $value) {
            if ($key!='title') $form .= ' ' . $key . '="' . $value . '"';
        }
        $this->addline($form . ' class="fullform">');
        if ($html) $this->addline($html);
        
        foreach ($contents as $id => $item) {
            $value = plaintext(isset($values[$id])?$values[$id]:'');
            $title = get_lang(($this->lang?$this->lang:$this->section),'input_'.$id);
            $label = true;
            // Allow array to override defaults
            if (is_array($item)) {
                if (isset($item['value'])) $value = $item['value'];
                if (isset($item['title'])) $title = $item['title'];
                if ( (isset($item['label'])) && ($item['label']==false) ) $label = false;
            }
            if (is_object($item)) {
                if ( (@isset($item->title)) && ($item->title) ) $title = $item->title;
                if ( (@isset($item->label)) && ($item->label == false) ) $label = false;
            }
            if ($label) $label = '    <label for="input_' . $id . '" class="inputlabel">' . $title . '</label>';
            
            if (is_object($item)) {
                $this->addline('    <fieldset id="field_'.$id.'" class="'.$item->type.'">');
                $this->addline($label);
                
                // Support field classes
                $response = $item->render($value,'input_'.$id);
                if (is_array($response)) {
                    // Array result for HTML & Javascript
                    if (isset($response['html'])) $this->addline($response['html']);
                    if (isset($response['js'])) $this->addjavascript($response['js']);
                // Simple HTML result
                } else $this->addline($response);
                $this->addline('    </fieldset>');
            } else {
                // Create manditory attributes
                if (!is_array($item)) $item = array('type'=>$item);
                if (!isset($item['id'])) $item['id'] = 'input_'.$id;
                if (!isset($item['name'])) $item['name'] = $item['id'];
                
                if ($item['type']=='html') {
                    $this->addline($item['html']);
                } else {
                    $this->addline('    <fieldset id="field_'.$item['id'].'" class="'.$item['type'].'">');
                    $this->addline($label);
                    
                    switch($item['type']) {
                        case 'textarea':
                            $input = '    <textarea';
                            foreach ($item as $key => $attr) {
                                if ($key != 'type') $input .= ' ' . $key . '="' . $attr . '"';
                            }
                            $this->addline($input . '>' . $value . '</textarea>');
                            break;
                        
                        case 'checkbox':
                            $input = '    <input type="hidden"';
                            foreach ($item as $key => $attr) {
                                if ($key != 'type') $input .= ' ' . $key . '="' . $attr . '"';
                            }
                            $this->addline($input . ' value="' . ($value?1:0) .'" />');
                            $this->addline('<div class="icheckbox" id="icheckbox_'.substr($item['id'],6).'"></div>');
                            break;
                        
                        case 'password_v':
                            if ($value) {
                                $this->addline('<img src="img/reset.gif" alt="reset" id="reset_'.$item['id'].'" class="password_v_reset" title="'.get_lang('admin','password_reset').'" />');
                                $value = '[no_change]';
                            }
                            
                            $item['type'] = 'password';
                            $item['class'] = 'password_v1';
                            $input = '    <input value="'.$value.'"';
                            foreach ($item as $key => $attr) $input .= ' ' . $key . '="' . $attr . '"';
                            $this->addline($input . ' />');
                            
                            $this->addjavascript('password_v($(\''.$item['id'].'\'),$(\'password_v_'.$item['id'].'\'),$(\''.$item['id'].'_v\'),$(\'reset_'.$item['id'].'\'));');
                            
                            $this->addline('<div id="password_v_'.$item['id'].'" class="password_v_div">');
                            $item['class'] = 'password_v2';
                            $item['name'] .= '_v';
                            $item['id'] .= '_v';
                            $this->addline('<label for="'.$item['id'].'" class="password_mid">'.get_lang('admin','password_v').'</label>');
                            $input = '    <input value="'.$value.'"';
                            foreach ($item as $key => $attr) $input .= ' ' . $key . '="' . $attr . '"';
                            $this->addline($input . ' />');
                            $this->addline('</div>');
                            break;
                        
                        case 'array':
                            $serialize = array();
                            if (!$value) $value = array();
                            foreach ($value as $k => $v) $serialize[$k] = plaintext(str_replace(array('&',','),array('&a','&c'),htmlspecialchars_decode($v,ENT_QUOTES)));
                            $serialize = implode(',',$serialize);
                            
                            $id = substr($item['id'],6);
                            $this->addline('<div class="array_hold" id="array_'.$id.'">');
                            $this->addline('<input type="hidden" value="'.$serialize.'" id="'.$item['id'].'" name="'.$item['name'].'" />');
                            $this->addline('<input type="text" id="arrinput_'.$id.'" value="" style="width:auto;" /> <img src="img/crystal/edit_add.png" alt="+" />');
                            $this->addline('<ul>');
                            $this->addline('</ul>');
                            $this->addline('<p style="clear:left;"></p></div>');
                            $this->addjavascript('createlistinput($(\'array_'.$id.'\'));');
                            $this->addjavascript('$(\'arrinput_'.$id.'\').addEvent(\'blur\',function(e) {alistitem(this);});
$(\'arrinput_'.$id.'\').addEvent(\'keydown\',function(e) {
    if ( (e.key == \'enter\') || (e.key == \'tab\') ) {
        $(\'arrinput_'.$id.'\').fireEvent(\'blur\');
        new Event(e).stop();
        return false;
    }
});');
                            break;
                        
                        default:
                            
                            $input = '    <input value="'.$value.'"';
                            foreach ($item as $key => $attr) $input .= ' ' . $key . '="' . $attr . '"';
                            $this->addline($input . ' />');
                            break;
                    }
                    $this->addline('    </fieldset>');
                }
            }
        }
        
        $this->addline('    <input type="hidden" name="submitkey" value="'.$this->submitkey().'" />');
        $this->addline('    <input type="submit" name="save" id="save" class="save" value="' . (isset($extra['submit'])?$extra['submit']:(isset($extra['title'])?$extra['title']:get_lang('title',$this->section . $GLOBALS['adminpage']->page))) . '" />');
        $this->addline('</form>');
    }
    
    function submitkey() {
        return $this->cpu('auth','encrypt',$this->cpu('auth','get','user','password'));
    }
    
    
    function readform(&$item,$fields=array(),$files=false) {
        setvar($item,'p');
        setvar($files,'f');
        
        if ( (!isset($item['submitkey'])) || ($item['submitkey'] != $this->submitkey()) ) {
            return $this->adderror(get_lang('admin','form_badkey'));
        }
        
        foreach ($fields as $id => $type) {
            if (is_array($type)) $type = $type['type'];
            if ($type != 'html') {
                if ($type !== 'file') {
                    if (!isset($item['input_'.$id])) {
                        $this->adderror(get_lang('admin','input_missing',$id));
                        return false;
                    } else {
                        $item[$id] = $item['input_'.$id];
                        unset($item['input_'.$id]);
                    }
                }
                
                if (is_object($type)) {
                    $item[$id] = $type->read($item[$id]);
                    if ( (is_array($item[$id])) && (isset($item[$id]['result'])) ) {
                        if ($item[$id]['result'] == 'bad') {
                            return false;
                        } else unset($item[$id]['result']);
                    }
                } else {
                    switch ($type) {
                        case 'password_v':
                            if ($item[$id] != $item['input_'.$id.'_v']) {
                                $this->adderror(get_lang('admin','password_v_fail'));
                                return false;
                            }
                            break;
                        
                        case 'file':
                            if (!isset($files['input_'.$id])) {
                                $this->adderror(get_lang('admin','input_missing',$id));
                                return false;
                            }
                            $files[$id] = $files['input_'.$id];
                            unset($files['input_'.$id]);
                            break;
                        
                        case 'array':
                            $item[$id] = explode(',',$item[$id]);
                            foreach ($item[$id] as $k => $v) $item[$id][$k] = str_replace(array('&c','&a'),array(',','&'),$v);
                    }
                }
            }
        }
        
        if ($files) return $files;
        if (isset($item['save'])) unset($item['save']);
        
        return true;
    }
    
    
}

if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string,$style=ENT_COMPAT) {
        $translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS,$style));
        if($style === ENT_QUOTES) {$translation['&#039;'] = '\'';}
        return strtr($string,$translation);
    }
}
