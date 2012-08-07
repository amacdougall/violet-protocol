<?php
import('.../lib/db/file/namebase.php');

class db_cache extends db_flib_namebase {
    // Index file layout
    var $layout = array(
        'name'=>'string',
        'comicmin'=>'string',
        'comicmax'=>'string',
        'comicfirst'=>'bool',
        'comiclast'=>'bool',
        'newsmin'=>'string',
        'newsmax'=>'string',
        'newsfirst'=>'bool',
        'newslast'=>'bool',
    );
    
    var $filepath = 'cache/';
    var $indexname = 'cache/index.db';
    
    
    
    
    function get($search=array()) {
        if ($search=='globals') {
            return array(array('name'=>'header'),array('name'=>'footer'));
        }
        
        if (!$this->getfile($this->indexname,true)) return array('result'=>'empty');
        
        $caches = parent::get();
        if ( (!is_array($search)) || (empty($search)) ) return $caches;
        parse_dbresult($caches);
        
        $result = array();
        
        foreach ($caches as $cache) {
            $c = count($result);
            foreach ($search as $check => $value) if (count($result)==$c) {
                if (substr($check,0,4)=='news') {
                    $type = 'news';
                } else $type = 'comic';
                
                switch ($check) {
                    case 'comic': case 'news':
                        if ( ($value=='+0') || ($value=='-0') ) {
                            if ( ($cache[$type.'min']===$value) || ($cache[$type.'max']===$value) ) {
                                $result[$cache['name']] = $cache;
                            } elseif( ($value==='+0') && ($cache[$type.'first']) ) {
                                $result[$cache['name']] = $cache;
                            } elseif( ($value==='-0') && ($cache[$type.'last']) ) {
                                $result[$cache['name']] = $cache;
                            }
                        } else {
                            $cpu = load_cpu($type);
                            if ($cache[$type.'max']==='+0') $cache[$type.'max'] = $cpu->firstid();
                            if ($cache[$type.'max']==='-0') $cache[$type.'max'] = $cpu->lastid();
                            if ($cache[$type.'min']==='+0') $cache[$type.'min'] = $cpu->firstid();
                            if ($cache[$type.'min']==='-0') $cache[$type.'min'] = $cpu->lastid();
                            if ( ($cache[$type.'min']<=$value) && ($cache[$type.'max']>=$value) ) $result[$cache['name']] = $cache;
                        }
                        break;
                    
                    case 'id':
                        $id = $search['id'];
                        if (isset($search['subid'])) $id.='.'.$search['subid'];
                        if (substr($cache['name'],0,(strlen($id)+1)) == $id.'.') $result[$cache['name']] = $cache;
                        break;
                    case 'subid':break;
                    
                    default:
                        if ($cache[$check]==$value) $result[$cache['name']] = $cache;
                        break;
                }
            }
        }
        
        if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    
    
    function delete($name) {
        if ($file = $this->getfile($this->filepath.filesafe($name.'.cache'),true)) $file->delete();
        return parent::delete($name);
    }
    
    
    function addindex($data) {
        foreach ($data['comic'] as $k => $v) $data['comic'.$k] = $v;
        foreach ($data['news'] as $k => $v) $data['news'.$k] = $v;
        $this->save($data);
    }
    
    function addfile($name,$content,$vars=false) {
        if ($vars) {
            $content = $this->store_array($vars)."\n".$content;
        } else $content = "\n".$content;
        $file = $this->getfile($this->filepath.filesafe($name.'.cache'));
        $file->write($content);
        $file->close();
    }
    
    
    
    function save_globals($globals) {
        foreach ($globals as $id => $content) {
            $file = $this->getfile($this->filepath.$id.'.cache');
            $file->write($content);
            $file->close();
        }
        return true;
    }
}