<?php
import('.../lib/admin/lib.php');

class admin_main extends admin_lib {
    var $section = 'main';
    var $navigation = array(
        'changelog'=>1,
        'license'=>1,
        'credits'=>1,
    );
    
    function d_main() {
        $this->newpage('main');
        
        if (!$this->cpu('auth','get','usergroup','permissions','panel')) {
            $this->addwarning(get_lang('main','nopanel'));
            return load_admin('special','editself');
        }
        
        $this->addline('<div style="float:left; width:500px; text-align:center; padding:10px 20px 0 130px; margin-right:20px; border-right:solid 1px #dbe1c5; background-image:url(img/magician.png); background-position:top left; background-repeat:no-repeat;">');
        $this->addline('<h2>' . get_lang('main','comicquicklinks') . '</h2>');
        
        $this->addline('<h3>' . get_lang('title','comicadd') . '</h3>');
        $this->addline('<form action="?p=comic:add" method="post" enctype="multipart/form-data" id="quickcomic"><input type="hidden" name="save" value="1" /><input type="file" name="input_comicimg" onchange="$(\'quickcomic\').submit();" /><input type="hidden" name="submitkey" value="'.$this->submitkey().'" /></form>');
        
        if ($comics = plaintext($this->cpu('comic','get','batch','-0','-2'))) {
            $this->addline('<h3>' . get_lang('title','comicedit') . '</h3>');
            $list = new admin_editlist;
            foreach ($comics as $comic) {
                $list->add(array(
                    'url'=>'?p=comic:edit&amp;comic='.$comic['id'],
                    'title'=>($comic['title']?$comic['title']:get_lang('comic','untitled')),
                ));
            }
            $this->addline($list->render());
        }
        
        $this->addline('<h2 style="padding-top:20px;">' . get_lang('main','newsquicklinks') . '</h2>');
        $this->addline('<a href="?p=news:add" class="onenav">'.get_lang('title','newsadd').'</a>');
        
        if ($news = plaintext($this->cpu('news','get','batch','-0','-2'))) {
            $this->addline('<h3>' . get_lang('title','newsedit') . '</h3>');
            $list = new admin_editlist;
            foreach ($news as $post) {
                $list->add(array(
                    'url'=>'?p=news:edit&amp;news='.$post['id'],
                    'title'=>($post['title']?$post['title']:get_lang('news','untitled')),
                ));
            }
            $this->addline($list->render());
        }
        
        $this->addline('</div>');
        
        
        
        $this->addline('<p>' . get_lang('main','mainpage1') . '</p>');
        
        $this->addline('<h2 style="text-align:left;">' . get_lang('main','helptitle') . '</h2>');
        $this->addline('<p>' . get_lang('main','help1') . '</p>');
        $this->addline('<p>' . get_lang('main','help2') . '</p>');
    }
    
    
    
    function d_license() {
        $this->newpage('license');
        
        $license = load_file('.../lib/license.txt');
        $this->addline('<pre>'.plaintext($license->read('string')).'</pre>');
    }
    
    function d_changelog() {
        $this->newpage('changelog');
        
        $changelog = load_file('.../lib/changelog.txt');
        $this->addline('<pre>'.plaintext($changelog->read('string')).'</pre>');
    }
    
    function d_credits() {
        $this->newpage('credits');
        
        $this->addline('<h3 style="text-align:left;">ComicCMS '.constant('ComicCMS').'</h3>');
        $this->addline('<ul style="font-size:90%;">');
        $this->addline('    <li>System designed and coded by <a href="http://fullvolume.co.uk/" target="_blank">Steve H</a></li>');
        $this->addline('    <li>Design and aditional images from <a href="http://wicked-moments.deviantart.com/" target="_blank">Shauni Lane</a></li>');
        $this->addline('    <li><a href="http://everaldo.com/crystal/" target="_blank">Crystal Project</a> icon pack <span style="font-size:60%;">(<a href="http://www.opensource.org/licenses/lgpl-2.1.php" target="_blank">LGPL license</a>) admin/img/crystal/</span></li>');
        $this->addline('    <li><a href="http://mootools.net/" target="_blank">Mootools</a> javascript framework <span style="font-size:60%;">(<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank">MIT license</a>) admin/inc/mootools.js</span></li>');
        $this->addline('    <li><a href="http://www.html.it/articoli/niftycube/index.html" target="_blank">Nifty Corners Cube</a> javascript library from Alessandro Fulciniti <span style="font-size:60%;">admin/inc/nifty/</span></li>');
        $this->addline('    <li><a href="http://digitarald.de/project/roar/" target="_blank">Roar</a> javascript notifications from Harald Kirschner <span style="font-size:60%;">(<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank">MIT license</a>) admin/inc/roar/</span></li>');
        $this->addline('    <li><a href="http://pear.php.net/pepr/pepr-proposal-show.php?id=198" target="_blank">PHP JSON library</a> from Michal Migurski <span style="font-size:60%;">(<a href="http://www.opensource.org/licenses/bsd-license.php" target="_blank">BSD license</a>) lib/class/json.php</span></li>');
        $this->addline('    <li><a href="http://nbbc.sourceforge.net/" target="_blank">NBBC</a> BBcode parser from Phantom Inker <span style="font-size:60%;">(<a href="http://www.opensource.org/licenses/bsd-license.php" target="_blank">BSD license</a>) lib/class/nbbc.php</span></li>');
        $this->addline('    <li>Special Thanks:<ul style="margin-top:0px; font-size:80%;">');
        $this->addline('        <li>Alpha Testers: <a href="http://comiccms.com/forum/index.php?action=profile;u=22" target="_blank">Arionhawk</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=60" target="_blank">Demongoldfish</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=27" target="_blank">Mastastealth</a></li>');
        $this->addline('        <li style="margin-bottom:10px;">Beta Testers: <div style="display:inline; font-size:90%;"><a href="http://comiccms.com/forum/index.php?action=profile;u=640" target="_blank">pwyll</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=730" target="_blank">Jimmy</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=30" target="_blank">hydriplex</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=152" target="_blank">Pocker09</a>, <a href="http://comiccms.com/forum/index.php?action=profile;u=664" target="_blank">bronzehedwick</a></div></li>');
        $this->addline('        <li><a href="http://www.not-crazy.com/" target="_blank">Tim</a> for your donation of $80</li>');
        $this->addline('        <li>Darryl V for your donation of $60</li>');
        $this->addline('        <li><a href="http://www.tiedyeheart.com/" target="_blank">Ina</a></li>');
        $this->addline('        <li><a href="http://www.championsofjustice.net/" target="_blank">Jesse Justice</a></li>');
        $this->addline('        <li style="margin-top:10px;">All the authors that have supported ComicCMS through ads on our main site</li>');
        $this->addline('    </ul></li>');
        $this->addline('</ul>');
        
        $this->addline('<p><a href="http://comiccms.com/" target="_blank">ComicCMS.com</a></p>');
        $this->addline('<p>With thanks to the forum members, bug reporters and die-hard users</p>');
    }
}
?>