<?php



////////////////////////////////////////////////////////////
// Map a directory as an array
////////////////////////////////////////////////////////////
function dir_array($dir,$sortorder=0,$ignore=true,$dirs=false) {
    if (canfind_dir($dir)) {
        $dirlist = opendir(makepath($dir));
        $files = array();
        
        while ( ($file = readdir($dirlist)) !== false) {
            if ( ( (!$ignore) || (!strpos($file,'~')) ) && ( ($dirs) || (!is_dir($file)) ) ) {
                $files[] = $file;
            }
        }
        
        ($sortorder == 0) ? asort($files) : rsort($files);
        return $files;
    } else {
        fatal_error('finddir',$dir);
        return false;
    }
}



function delete_directory($dir) {
    $dir = array($dir);
    while ($cur = array_shift($dir)) {
        if (canfind_dir($cur)) {
            $add = dir_array($cur,0,false,false);
            if (count($add)>0) {
                while (count($add)>0) $dir[] = foldername($cur.'/').array_pop($add);
                $dir[] = $cur;
            } else rmdir(makepath($cur));
        } elseif (canfind_file($cur)) {
            delete_file($cur);
        }
    }
}



function make_directory($dir,$chmod=false) {
    $dir = foldername($dir);
    $queue = array();
    
    // Queue folders to be created
    while (!canfind_dir($dir)) {
        $queue[] = $dir;
        $dir = foldername(substr($dir,0,(strlen($dir)-1)));
    }
    
    // Create folders
    while ($dir = array_pop($queue)) {
        if ($chmod) {
            mkdir($dir,octdec('0'.$chmod));
        } else {
            mkdir($dir);
            chmod($dir,0777);
        }
    }
}




////////////////////////////////////////////////////////////
// Write to a file
////////////////////////////////////////////////////////////
function write_file($file,$mode,$string,$chmod=false) {
    $string = (is_array($string)?implode($string):$string);
    $new = false;
    if (!canfind_file($file)) {
        $new = true;
        $file = makepath($file);
        $dir = foldername($file);
        if (!canfind_dir($dir)) {
            fatal_error('findfile',$file);
            return false;
        }
        if (!canwrite($dir)) {
            fatal_error('writedir',$dir . ' [' . filename($file) . ']');
            return false;
        }
    } elseif (!canwrite($file)) {
        fatal_error('writefile',$file);
        return false;
    }
    
    delmem('f_' . $file);
    
    $file = makepath($file);
    $fhandle = fopen($file,$mode);
    fwrite($fhandle,$string);
    fclose($fhandle);
    if ($chmod) {
        @chmod($file,octdec('0'.$chmod));
    } elseif ($new) {
        @chmod($file,0666);
    }
    return true;
}



////////////////////////////////////////////////////////////
// Delete file
////////////////////////////////////////////////////////////
function delete_file($file) {
    if ( (canfind_file($file)) && (!@unlink(makepath($file))) ) {
        fatal_error('deletefile',$file);
        return false;
    }
    return true;
}



////////////////////////////////////////////////////////////
// Copy file
////////////////////////////////////////////////////////////
function copy_file($from,$file,$chmod=false) {
    $from = makepath($from);
    $file = makepath($file);
    if (!canfind_file($from)) fatal_error('findfile',$from);
    
    $new = false;
    if (!canfind_file($file)) {
        $new = true;
        $dir = foldername($file);
        if (!canfind_dir($dir)) fatal_error('findfile',$file);
        if (!canwrite($dir)) fatal_error('writedir',$dir . ' [' . substr($file,(strrpos($file,'/')+1)) . ']');
    } elseif (!canwrite($file)) fatal_error('writefile',$file);
    
    copy($from,$file);
    if ($chmod) {
        chmod($file,octdec('0'.$chmod));
    } elseif ($new) {
        chmod($file,0666);
    }
    return true;
}



////////////////////////////////////////////////////////////
// Move file
////////////////////////////////////////////////////////////
function move_file($from,$to,$chmod=false) {
    if (copy_file($from,$to,$chmod)) {
        delete_file($from);
        return true;
    }
    return false;
}



////////////////////////////////////////////////////////////
// Rename file
////////////////////////////////////////////////////////////
function rename_file($from,$name) {
    return move_file($from,foldername($from).filename($name));
}



////////////////////////////////////////////////////////////
// CHMOD file
////////////////////////////////////////////////////////////
function chmod_file($file,$chmod) {
    if (canfind_file($file)) {
        return @chmod(makepath($file),$chmod);
    } else return false;
}

?>