<?php
//
//  Comic Dropdown Form
//  -------------------
//  - Author: Steve H <http://comiccms.com/>
//  - License: GPL 3 <http://www.gnu.org/copyleft/gpl.html>
//  - Created: May 2008
//  - Updated: July 2009
//
//  Designed for ComicCMS 0.1.9b <http://comiccms.com/>
//
class pl_dropdownform extends ComicCMS {
    var $id = 'dropdownform';
    var $version = '1';
    var $forversion = array('0.1.9alpha7','0.1.9b','0.2RC1','0.2');
    var $template = true;
    
    var $lang = array(
        '_lang'=>'en',
        'en'=>array(
            '_title'=>'Comic Dropdown Form',
            '_desc'=>'Creates a dropdown box to navigate through your comics',
            '_author'=>'Steve H',
            
            'empty'=>'dropdownform: No comics to display',
            
            'mainpage'=>'This plugin creates a dropdown box for your comic site. To use it place {{plugin:dropdownform}} anywhere in your templates. By default, if it is placed where {{comic_id}} is avaliable it will auto-select that comic. If it is placed outside of a comic area it will display the most recent comic by default.',
            
            'head'=>'Head Template',
            'item'=>'Each Comic',
            'foot'=>'Foot Template',
            'return'=>'Return Patch',
            
            'remove'=>'Erase all instances of {{plugin:dropdownform}} from your templates',
            'deletefail'=>'An error occurred which stopped the dropdownform plugin from being deleted',
        ),
    );
    
    var $settings = array(
        'template'=>array(
            'head'=>'<form action="http:///" method="get" id="dropdownform">
  <select name="id" onchange="document.getElementById(\'dropdownform\').submit();">',
            'item'=>'    <option value="{{comic_id}}" id="dropdown_{{comic_id}}">{{comic_id}}: {{if:comic_title}}{{comic_title}}{{else}}Untitled Comic{{endif}}</option>',
            'foot'=>'  </select>
    <input type="submit" value="Go" />
</form>',
            'return'=>'{{if:comic_id}}<script type="text/javascript"><!--
  document.getElementById(\'dropdown_{{comic_id}}\').setAttribute(\'selected\',\'selected\');
--></script>{{endif}}',
        ),
    );
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Generate default form fields
    ////////////////////////////////////////////////////////////
    function fields($switch) {
        switch ($switch) {
            case 'edit':
                return array(
                    'head'=>array('type'=>'textarea','title'=>$this->lang('head'),'class'=>'resizeTextarea'),
                    'item'=>array('type'=>'textarea','title'=>$this->lang('item'),'class'=>'resizeTextarea'),
                    'foot'=>array('type'=>'textarea','title'=>$this->lang('foot'),'class'=>'resizeTextarea'),
                    'return'=>array('type'=>'textarea','title'=>$this->lang('return'),'class'=>'resizeTextarea'),
                );
                break;
            
            case 'delete':
                return array(
                    'remove'=>array('type'=>'checkbox','title'=>$this->lang('remove')),
                    'fullremove'=>'checkbox',
                );
                break;
        }
    }
    
    
    function d_use($admin) {
        $admin->addline('<p>'.$this->lang('mainpage').'</p>');
    }
    
    
    function d_edit($admin,$data=false) {
        $this->settings = ($data?$data:$this->settings);
        $admin->genform($this->fields('edit'),$this->settings['template']);
    }
    function p_edit($admin,$data) {
        if (!$admin->readform($data,$this->fields('edit'))) return false;
        
        $this->settings['template'] = $data;
        $this->save();
        return true;
    }
    
    
    function d_delete($admin,$data=false) {
        $admin->genform($this->fields('delete'),array('remove'=>1));
    }
    function p_delete($admin,$data) {
        if (!$admin->readform($data,$this->fields('delete'))) return false;
        
        if ( (!$data['remove']) || ($admin->cpu('template','delete_content','{{plugin:dropdownform}}')) ) {
            $this->delete($data['fullremove']);
            return true;
        } else {
            $admin->adderror($this->lang('deletefail'));
            return false;
        }
    }
    
    
    
    
    
    
    
    
    
    function cacherebuild() {
        import('.../lib/site/template.php');
        $tpl = new site_template;
        
        $content = '';
        $comics = $tpl->get('comic','range','-0','+0');
        
        if ($comics) {
            $content .= $tpl->parse($this->settings['template']['head']) . "\n";
            foreach ($comics as $comic) {
                $content .= $tpl->parse($this->settings['template']['item'],'comic',$comic) . "\n";
            }
            $content .= $tpl->parse($this->settings['template']['foot']);
        } else {
            $content .= $this->lang('empty');
        }
        
        $tpl->addcache($content,'pl_'.$this->id);
        
        return $content;
    }
    
    function plugintpl($extra) {
        return '{{plugincache:dropdownform}}' . $this->settings['template']['return'];
    }
}
?>