<?php

// 
// Image manipulation class
// 
// 
// 

class class_image {
    var $uri = false;
    var $img = false;
    var $width = 0;
    var $height = 0;
    
    
    function load($uri) {
        $uri = makepath($uri);
        @list($width,$height,$typeid) = @getimagesize($uri);
        switch($typeid) {
            case 1: $this->img = imagecreatefromgif($uri); break;
            case 2: $this->img = imagecreatefromjpeg($uri); break;
            case 3: $this->img = imagecreatefrompng($uri); break;
            default: return false; break;
        }
        
        $this->uri = $uri;
        $this->width = $width;
        $this->height = $height;
        return true;
    }
    
    
    function replace($newimg) {
        imagedestroy($this->img);
        $this->img = $newimg;
        
        $this->width = imagesx($this->img);
        $this->height = imagesy($this->img);
    }
    
    
    function value($value,$direction='h',$inherited=false) {
        if (!is_numeric($value)) {
            if ($pos = strpos($value,'%')) {
                $value = substr($value,0,$pos);
                $full = ($direction=='w'?$this->width:$this->height);
                $value = ($full/100)*$value;
            } else $value = 0;
        } elseif ($inherited) {
            if ($direction=='w') {
                $diff = $this->height/$value;
                $value = round($this->width/$diff);
            } else {
                $diff = $this->width/$value;
                $value = $this->height/$diff;
            }
        }
        
        return round($value);
    }
    
    
    
    function position(&$top,&$right,&$bottom,&$left) {
        $right = ($right==='inherit'?$this->value($top,'w',true):$this->value($right,'w'));
        $top = $this->value($top,'h');
        $bottom = ($bottom==='inherit'?$top:$this->value($bottom,'h'));
        $left = ($left==='inherit'?$right:$this->value($left,'w'));
    }
    
    function size(&$width,&$height) {
        if ($width === 'inherit') {
            $width = $this->value($height,'w',true);
            $height = $this->value($height,'h');
        } else {
            $height = ($height==='inherit'?$this->value($width,'h',true):$this->value($height,'h'));
            $width = $this->value($height,'w',true);
        }
    }
    
    
    
    function trim($top,$right='inherit',$bottom='inherit',$left='inherit') {
        $this->position($top,$right,$bottom,$left);
        
        $height = $this->height - ($top + $bottom);
        $width = $this->width - ($right + $left);
        
        $newimg = imagecreatetruecolor($width,$height);
        imagecopy($newimg,$this->img,0,0,$left,$top,$width,$height);
        $this->replace($newimg);
    }
    
    function resize($width='inherit',$height='inherit') {
        $this->size($width,$height);
        
        $newimg = imagecreatetruecolor($width,$height);
        imagecopyresampled($newimg,$this->img,0,0,0,0,$width,$height,$this->width,$this->height);
        $this->replace($newimg);
    }
    
    
    
    function render_jpeg($filename=null,$quality=80) {
        return imagejpeg($this->img,makepath($filename),$quality);
    }
    
    function render_png($filename=null,$quality=9) {
        imagealphablending($this->img,true);
        imagesavealpha($this->img,true);
        return imagepng($this->img,makepath($filename),$quality);
    }
    
    function render_gif($filename=null) {
        return imagegif($this->img,makepath($filename));
    }
    
    
    function render_html() {
        ob_start();
        $this->render_png();
        $img = ob_get_contents();
        ob_end_clean();
        echo '<div style="float:left; padding:5px;"><h2>PNG ('.ceil(strlen($img)/1024).'kb)</h2>';
        echo '<img src="data:image/png;base64,'.base64_encode($img).'" /></div>';
        
        ob_start();
        $this->render_jpeg();
        $img = ob_get_contents();
        ob_end_clean();
        echo '<div style="float:left; padding:5px;"><h2>JPEG ('.ceil(strlen($img)/1024).'kb)</h2>';
        echo '<img src="data:image/jpeg;base64,'.base64_encode($img).'" /></div>';
        
        ob_start();
        $this->render_gif();
        $img = ob_get_contents();
        ob_end_clean();
        echo '<div style="float:left; padding:5px;"><h2>GIF ('.ceil(strlen($img)/1024).'kb)</h2>';
        echo '<img src="data:image/gif;base64,'.base64_encode($img).'" /></div>';
    }
}

?>
