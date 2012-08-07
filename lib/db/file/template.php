<?php
import('.../lib/db/file/lib.php');

class db_template extends db_flib {
    // Template file layout
    var $layout = array(
        0=>array(
            'contenttype'=>'string',
            'encase'=>'bool',
            'trigger'=>'string',
            'type'=>'string',
            'function'=>'string',
            'source'=>'array',
        ),
        1=>array(
            'vars'=>'storedarray',
        ),
    );
    
    var $basetype = 'id';
    var $foldername = 'templates/pages/';
    
    
    
    function get($page,$id=false) {
        if ($id) {
            // Get template
            if ($file = $this->templatefile($page,$id,true)) {
                $template = $this->loaddata($file->read('array'));
                $template['id'] = $id;
                $template['result'] = 'good';
                return $template;
            } else return array('result'=>'error');
        } else {
            // Load a page full of templates
            $pagedb = load_db('file');
            $page = $pagedb->get($page);
            if (parse_dbresult($page)) { // Check page exists
                if (!empty($page['templates'])) { // Check page has templates
                    // Sift through each template
                    $result = array();
                    foreach ($page['templates'] as $template) {
                        $result[$template['id']] = $this->get($page['name'],$template['id']);
                    }
                    if (!isset($result['result'])) $result['result'] = (empty($result)?'empty':'good');
                    return $result;
                } else return array('result'=>'empty');
            } else return array('result'=>'error');
        }
    }
    
    
    function get_globals() {
        $header = $this->getfile('templates/header.tpl');
        $header = $header->read('array');
        $footer = $this->getfile('templates/footer.tpl');
        $footer = $footer->read('array');
        
        return array(
            'header'=>array(
                'vars'=>$this->unstore_array(array_shift($header)),
                'content'=>rtrim(implode($header)),
            ),
            'footer'=>array(
                'vars'=>$this->unstore_array(array_shift($footer)),
                'content'=>rtrim(implode($footer)),
            ),
        );
    }
    function save_globals($globals) {
        $headerfile = $this->store_array($globals['header']['vars'])."\n".templatetext('store',$globals['header']['content']);
        $footerfile = $this->store_array($globals['footer']['vars'])."\n".templatetext('store',$globals['footer']['content']);
        
        $class = $this->getfile('templates/header.tpl');
        $class->write($headerfile);
        $class = $this->getfile('templates/footer.tpl');
        $class->write($footerfile);
    }
    
    
    function save($template,$page,$id=false) {
        if ($id) {
            // Allow for partial updates
            $original = $this->get($page,$id);
            if (parse_dbresult($original)) $template = array_merge($original,$template);
        } else {
            // Create new template if needed
            $pagedb = load_db('file');
            $pagearr = $pagedb->get($page);
            $id = count($pagearr['templates'])+1;
        }
        
        // Write template
        $file = $this->templatefile($page,$id);
        $file->write($this->savedata($template));
        
        return $id;
    }
    
    
    
    function delete($page,$id) {
        // Delete template file
        if ($file = $this->templatefile($page,$id,true)) $file->delete();
    }
    
    
    function loaddata($file) {
        $template = array();
        
        // Pull data from file array
        foreach ($this->layout as $line => $layout) {
            $template += $this->linetodata($file[$line],$layout);
            unset($file[$line]);
        }
        $template['content'] = implode($file);
        
        return $template;
    }
    function savedata($template) {
        $file = array();
        
        // Format data into file array
        foreach ($this->layout as $line => $layout) {
            $file[$line] = $this->datatoline($template,$this->layout[$line]);
        }
        $file[] = $template['content'];
        
        // Ensure line breaks throughout file
        foreach ($file as $key => $line) {
            if (substr($line,-1)!="\n") $file[$key] .= "\n";
        }
        
        return implode($file);
    }
    
    
    
    function templatefile($pageid,$templateid,$check=false) {
        return $this->getfile($this->foldername.filesafe(str_replace('/','.',$pageid)).'.'.filesafe($templateid).'.tpl',$check);
    }
}
?>