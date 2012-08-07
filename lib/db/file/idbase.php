<?php
import('.../lib/db/file/lib.php');

////////////////////////////////////////////////////////////
// Extendable class for items that use numeric IDs
// Acts as a base for comics and news (or anything else that wants it)
////////////////////////////////////////////////////////////
class db_flib_idbase extends db_flib {
    var $itemcache = array();
    
    var $basetype = 'id';
    
    // Default data, extending classes should change these
    // Set perfile as 0 and filename as the path to the file to only use one file
    var $perfile = 0;
    var $filepath = false;
    var $filename = false;
    
    
    
    function clearcache() {
        $this->itemcache = array();
        parent::clearcache();
    }
    
    
    ////////////////////////////////////////////////////////////
    // Return item
    // Accepts input in the form of $this->getrange()
    //   get() will return all items
    //   get(49) or get('single',49) will return item # 49
    //   get('range',4,9) will return all items between 4 & 9 inclusive
    //   get('batch',4,7,-2) will return item 4 if it exists, 7 items after it and 2 items before it
    ////////////////////////////////////////////////////////////
    function get() {
        // Define range to fetch
        $range = $this->getrange(func_get_args());
        $min = min($range['from'],$range['to']);
        $max = max($range['from'],$range['to']);
        $reverse = ($min==$range['from']?false:true);
        
        // Fix the range blanks
        if ( ($min==0) && ($max==0) ) return array('result'=>'empty');
        if ($min==0) $min=$max;
        if ($max==0) $max=$min;
        
        $result = array();
        
        foreach (range($min,$max) as $id) {
            if (isset($this->itemcache[$id])) {
                // Already loaded & cached
                $result[$id] = $this->itemcache[$id];
            } else {
                if ( (!isset($file)) || ($id > $file->data['lastid']) || ($id < $file->data['firstid']) ) {
                    // Load new file if needed
                    $file = $this->itemfile($id,true);
                    if (!$file) return array('result'=>'error');
                }
                
                // Find item
                $line = $file->read(($id-$file->data['firstid']));
                if ( ($line) && (strpos($line,'|')!==false) ) {
                    // Format data
                    $item = $this->loaddata($line,$id);
                    if ( (!$range['tag']) || (in_array($range['tag'],$item['tags'])) ) {
                        $result[$id] = $item;
                    }
                }
            }
        }
        
        // Format response
        if ($range['single']) $result = array_shift($result);
        if ($reverse) $result = array_reverse($result,true);
        if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Save item
    //   save($item) will save $item as a new item
    //   save($item,49) will save $item as item #49
    ////////////////////////////////////////////////////////////
    function save($item,$id=false) {
        if (!is_array($item)) return false;
        
        if ($id) {
            // Allow for partial updates
            $original = $this->get($id);
            if (parse_dbresult($original)) $item = array_merge($original,$item);
        }
        
        $line = $this->savedata($item);
        
        if ($id) {
            $file = $this->itemfile($id);
            $arr = $file->read('array');
            $arr[($id-$file->data['firstid'])] = $line;
            $file->write($arr);
        } else {
            $id = $this->lastid()+1;
            $file = $this->itemfile($id);
            $file->append($line);
        }
        
        $this->clearcache();
        
        return $id;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Delete item
    //    delete(49) will delete item #49
    ////////////////////////////////////////////////////////////
    function delete($id) {
        $id = $this->getid($id);
        $file = $this->itemfile($id);
        $arr = $file->read('array');
        
        
        if ($id==$this->lastid()) { // Latest item - hard delete
            unset($arr[($id-$file->data['firstid'])]);
            
            // Cleanup
            while (strpos(end($arr),'|')===false) {
                array_pop($arr);
                $id--;
                if ($id <= $file->data['firstid']) {
                    $file->delete(); // No items left in that file
                    if (!$file = $this->itemfile($id,true)) {
                        $this->clearcache();
                        return $id;
                    }
                    $arr = $file->read('array');
                }
            }
        } else {
            $arr[($id-$file->data['firstid'])] = '*Deleted*'."\n"; // Old item - keep ID gap
        }
        
        $file->write($arr);
        $this->clearcache();
        
        return $id;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Search items
    //    search('foo') will find all items with foo anywhere in their fields
    //    search('foo','title') will find all items with foo as the title
    //
    // With $match = false only return if attribute is the query
    //
    // Search is still primitive, please put as little strain on it as possible
    ////////////////////////////////////////////////////////////
    function search($find,$attribute=false,$partial=false) {
        $id = $this->lastid();
        
        $result = array();
        while ($id > 0) {
            if ( (!isset($file)) || ($id < $file->data['firstid']) ) {
                // Load new file if needed
                $file = $this->itemfile($id,true);
                if (!$file) return array('result'=>'error');
            }
            
            // Quick search
            $line = $file->read(($id-$file->data['firstid']));
            if (strpos(' '.strtolower($line),strtolower($find))) {
                // In depth search & format
                $comic = $this->loaddata($line,$id);
                if (!$attribute) {
                    $result[$id] = $comic;
                } elseif (is_array($comic[$attribute])) {
                    if (in_array($find,$comic[$attribute])) $result[$id] = $comic;
                } elseif ( ($partial) && (strpos(' '.strtolower($comic[$attribute]),strtolower($find))) ) {
                    $result[$id] = $comic;
                } elseif (strtolower($comic[$attribute])==strtolower($find)) {
                    $result[$id] = $comic;
                }
            }
            $id--;
        }
        
        // Format response
        if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
        return $result;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Turn storage line into item data
    ////////////////////////////////////////////////////////////
    function loaddata($line,$id=false) {
        $item = $this->linetodata($line,$this->layout);
        
        // Expand timestamp & add variables
        if (isset($item['timestamp'])) {
            $item += $this->timestamp($item['timestamp']);
        }
        
        // Join user data
        if (isset($item['author'])) {
            $userdb = load_db('user');
            $item['author'] = $userdb->get($item['author']);
            if (parse_dbresult($item['author'])) {
                unset($item['author']['session']);
                unset($item['author']['password']);
                foreach($item['author'] as $name => $value) $item['user' . $name] = $value;
            }
        }
        
        // ID related variables
        if ($id) {
            $item['id'] = $id;
            $item['islast'] = ($item['id']==$this->lastid()?true:false);
            $item['isfirst'] = ($item['id']==$this->firstid()?true:false);
            
            $this->itemcache[$id] = $item;
        }
        
        return $item;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Turn item data into storage line
    ////////////////////////////////////////////////////////////
    // PRIVATE
    function savedata($item) {
        return $this->datatoline($item,$this->layout);
    }
    
    
    
    ///////////////////////////////////////////////////////////
    // Load item file from ID
    ////////////////////////////////////////////////////////////
    // PRIVATE
    function itemfile($id,$check=false) {
        if (!$this->perfile) {
            // One file only
            $file = $this->filename;
        } else $file = $this->filepath.$this->rangename($id,$this->perfile);
        
        if (!$file = $this->getfile($file,$check)) return false;
        
        if (!$this->perfile) {
            // One file only
            $file->data['firstid'] = 1;
            $file->data['lastid'] = 99999999;
        } else {
            // Range file data
            $file->data['firstid'] = (floor(($id-1)/$this->perfile)*$this->perfile)+1;
            $file->data['lastid'] = ceil($id/$this->perfile)*$this->perfile;
        }
        return $file;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Return ID of first item
    //   firstid() will return the ID of first (non-deleted) item
    //   firstid('foo') will return the ID of the first item with the tag foo
    ////////////////////////////////////////////////////////////
    function firstid($tag=false) {
        if (isset($this->cache['f' . $tag])) return $this->cache['f' . $tag];
        
        if (!$tag) {
            // No tag, work it out here
            
            // Load first item file
            $id = 1;
            $file = $this->itemfile($id,true);
            
            if ( ($file) && (rtrim($file->read(0))) ) {
                // Skip deleted items
                while (strpos($file->read(($id-$file->data['firstid'])),'|')===false) {
                    $id++;
                    // A whole file full of deletions!?
                    if ($id > $file->data['lastid']) $file = $this->itemfile($id);
                }
            } else $id=0;
            
        // Has tag, use other function
        } else $id = $this->getid('+0',$tag);
        
        $this->cache['f' . $tag] = $id;
        return $id;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Return ID of last item
    //   lastid() will return the ID of last item
    //   lastid('foo') will return the ID of the last item with the tag foo
    ////////////////////////////////////////////////////////////
    function lastid($tag=false) {
        if (isset($this->cache['l' . $tag])) return $this->cache['l' . $tag];
        
        if (!$tag) {
            // No tag, work it out here
            
            // Load last item file
            $id = 1;
            if ($this->perfile==0) {
               if (!$file = $this->itemfile(1,true)) $id=0;
            } else {
                while ($this->itemfile($id,true)) $id += $this->perfile;
                $id -= $this->perfile;
                if (!$file = $this->itemfile($id,true)) $id=0;
            }
            $arr = ($file?$file->read('array'):array());
            if (!$arr) $id=0;
            
            // Skip deleted items
            while ( ($id!=0) && (strpos(end($arr),'|')===false) ) {
                array_pop($arr);
                if (empty($arr)) {
                    // This file is all out of items
                    $id -= $this->perfile;
                    if ($id > 0) {
                        // Go back a file and continue the clearup
                        $file = $this->itemfile($id);
                        $arr = $file->read('array');
                    } else $id = 0; // No items left
                }
            }
            if ($id != 0) $id = key($arr) + $file->data['firstid'];
            
        // Has tag, use other function
        } else $id = $this->getid('-0',$tag);
        
        $this->cache['l' . $tag] = $id;
        return $id;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Return real ID from offset
    //   getid('+3') will return the third from the beginning
    //   getid('-3') will return the third from the end
    //   getid('-3','foo') will return the third from the end.
    //                     Will only count those with the tag foo.
    ////////////////////////////////////////////////////////////
    function getid($id,$tag=false) {
        if (is_string($id)) {
            if (substr($id,0,1)=='+') {
                $diff = substr($id,1)*1;
                
                if ($this->firstid()==0) return 0;
                
                // Allow +0
                if ($diff==0) {
                    $id = $this->getrange('getid',$this->firstid(),1,0,$tag);
                    if (is_array($id)) $id = 0;
                } else {
                    $start = $this->getrange('batch',$this->firstid(),$diff,0,$tag);
                    $id = $start['to'];
                }
            } elseif (substr($id,0,1)=='-') {
                $diff = substr($id,1)*-1;
                
                if ($this->firstid()==0) return 0;
                
                // Allow -0
                if ($diff==0) {
                    $id = $this->getrange('getid',$this->lastid(),-1,0,$tag);
                    if (is_array($id)) $id = 0;
                } else {
                    $start = $this->getrange('batch',$this->lastid(),$diff,0,$tag);
                    $id = $start['to'];
                    if ($id==1) return 0; // Error
                }
            } else {
                $id *= 1;
            }
        }
        if ($id == 0) $id = $this->getid('-0',$tag);
        return $id;
    }
    
    
    
    ////////////////////////////////////////////////////////////
    // Get range
    //   Takes the same arguments as get()
    //
    // Returns an array():
    //   'from' => first ID
    //   'to' => last ID
    //   'tag' => tag
    //   'single' => true if only 1 was requested
    //               (rather than a range requested and only one found)
    ////////////////////////////////////////////////////////////
    function getrange() {
        // Get arguments (can be an array)
        $vars = func_get_args();
        while ( (isset($vars[0])) && (is_array($vars[0])) ) $vars = $vars[0];
        
        // Sanitize quick-selects
        if (!isset($vars[0])) $vars = array('all');
        if ( (is_numeric($vars[0])) || (substr($vars[0],0,1) == '-') || (substr($vars[0],0,1) == '+') ) {
            array_unshift($vars,'single');
        }
        
        // Return behaviour
        switch ($command = array_shift($vars)) {
            case 'all':
                $tag = (isset($vars[0])?$vars[0]:false);
                return array(
                    'from'=>$this->firstid($tag),
                    'to'=>$this->lastid($tag),
                    'tag'=>$tag,
                    'single'=>false,
                );
                break;
            
            case 'single':
                $tag = (isset($vars[1])?$vars[1]:false);
                $id = $this->getid($vars[0],$tag);
                return array(
                    'from'=>$id,
                    'to'=>$id,
                    'tag'=>$tag,
                    'single'=>true,
                );
                break;
            
            case 'range':
                $tag = (isset($vars[2])?$vars[2]:false);
                return array(
                    'from'=>$this->getid($vars[0],$tag),
                    'to'=>$this->getid($vars[1],$tag),
                    'tag'=>$tag,
                    'single'=>false,
                );
                break;
            
            case 'batch':
            case 'getid':
                // We're going to be here a while, so name friendly
                if ($vars[1]>0) {
                    $plus = $vars[1];
                } elseif ($vars[1]<0) $minus = $vars[1];
                if (isset($vars[2])) {
                    if (is_numeric($vars[2])) {
                        if ($vars[2]>0) {
                            $plus = $vars[2];
                        } elseif ($vars[2]<0) $minus = $vars[2];
                    } else $tag = $vars[2];
                }
                if (!isset($tag)) $tag = (isset($vars[3])?$vars[3]:false);
                
                if (!isset($plus)) $plus = 0;
                if (!isset($minus)) $minus = 0;
                
                $id = $this->getid($vars[0],$tag);
                
                
                // Run up the items
                $plusid = $id;
                $plus++; // Include current id
                $file = false;
                
                while ( ($plus > 0) && ($plusid > 0) ) {
                    if ( (!$file) || ($plusid > $file->data['lastid']) ) {
                        if (!$file = $this->itemfile($plusid,true)) {$plus = 0; $plusid--;}
                    }
                    
                    if ($file) {
                        // Find item
                        $line = $file->read(($plusid-$file->data['firstid']));
                        if ( ($line) && (strpos($line,'|')!==false) ) {
                            if ( (!$tag) || ( ($item = $this->loaddata($line,$plusid)) && (in_array($tag,$item['tags'])!==false) ) ) {
                                $plus--;
                                if ($command == 'getid') return $plusid;
                            }
                        } elseif ($plusid == $id) $plus--; // Unhack including current ID
                    }
                    
                    $plusid++; // Next item
                }
                $plusid--;
                
                
                // Run down the items
                $minusid = $id;
                $minus--; // Include current id
                $file = false;
                
                while ( ($minus < 0) && ($minusid > 0) ) {
                    if ( (!$file) || ($minusid < $file->data['firstid']) ) {
                        if (!$file = $this->itemfile($minusid,true)) {$minus = 0; $minusid++;}
                    }
                    
                    if ($file) {
                        // Find item
                        $line = $file->read(($minusid-$file->data['firstid']));
                        if ( ($line) && (strpos($line,'|')!==false) ) {
                            if ( (!$tag) || ( ($item = $this->loaddata($line,$minusid)) && (in_array($tag,$item['tags'])!==false) ) ) {
                                $minus++;
                                if ($command == 'getid') return $minusid;
                            }
                        } elseif ($minusid == $id) $minus++; // Unhack including current ID
                    }
                    
                    $minusid--; // Prev item
                }
                $minusid++;
                
                
                // Format response
                if ($vars[1]>0) {
                    return array(
                        'from'=>$minusid,
                        'to'=>$plusid,
                        'tag'=>$tag,
                        'single'=>false,
                    );
                } else {
                    return array(
                        'from'=>$plusid,
                        'to'=>$minusid,
                        'tag'=>$tag,
                        'single'=>false,
                    );
                }
                break;
            
            default: return $vars[0]; break;
        }
    }
}
?>