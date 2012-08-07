<?php

// 
// External download class
// Handles downloads over HTTP
// 
// (c) Steve H 2008 :: GPL v3
// 

class class_download {
    
    
    function download($url,$dest=false) {
        if ($dest) {
            $stream = fopen(makepath($dest),'wb');
        } else $stream = false;
        
        $response = $this->download_curl($url,$stream);
        if (!$response) {
            $response = $this->download_fopen($url,$stream);
            if (!$response) {
                $response = $this->download_fsock($url,$stream);
            }
        }
        return $response;
    }
    
    
    
    function download_curl($url,$stream=false) {
        if (!function_exists('curl_init')) return false;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        
        if ($stream) {
            curl_setopt($ch, CURLOPT_FILE, $stream);
        } else curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    
    
    
    function download_fopen($url,$stream=false) {
        if (!$file = @fopen($url,'rb')) return false;
        
        if ($stream) {
            $r = true;
            while ($block = fread($file,512)) fwrite($stream,$block);
        } else {
            $r = '';
            while ($block = fread($file,512)) $r .= $block;
        }
        
        fclose($file);
        return $r;
    }
    
    
    
    function download_fsock($url,$stream=false) {
        $data = parse_url($url);
        
        if (!$connect = @fsockopen($data['host'],80,$errno,$errstr)) return false;
        
        fwrite($connect, "GET ".$data['path'].(isset($data['query'])?'?'.$data['query']:'')." HTTP/1.1\r\n");
        fwrite($connect, "Host: ".$data['host']."\r\n");
        fwrite($connect, "Connection: Close\r\n\r\n");
        
        // Skip HTTP headers
        $stop = false;
        while (!$stop) {
            $r = str_replace("\r",'',fgets($connect));
            if (strpos($r,'404 Not Found')) return false;
            if ($r == "\n") $stop = true;
        }
        
        if ($stream) {
            $r = true;
            while(!feof($connect)) fwrite($stream,fread($connect,512));
        } else {
            $r = '';
            while(!feof($connect)) $r .= str_replace("\r",'',fread($connect,512));
        }
        fclose($connect);
        
        return $r;
    }
    
}