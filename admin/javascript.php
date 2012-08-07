/********************************************************************
//                   ComicCMS Javascript Library
//                   ===========================
//                   Copyright (C) 2007 Steve H
//                      http://ComicCMS.com/
//-------------------------------------------------------------------
//  This program is free software; you can redistribute it and/or
//  modify it under the terms of the GNU General Public License
//  as published by the Free Software Foundation; either version 2
//  of the License, or (at your option) any later version.
//  
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//  
//  http://www.gnu.org/copyleft/gpl.html
//
********************************************************************/





function login_a(e,form,refresh,username,userpass) {
    if (refresh==1) {
        form.target = 'preloadframe';
        $('preloadframe').addEvent('load',function () {
            this.removeEvents('load');
            window.frames['iloadframe'].location.reload(true);
        });
    }
}


////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// Page functions //////////////////////////////////////



////////////////////////////////////////////////////////////
// Refresh capture
////////////////////////////////////////////////////////////
window.addEvent('keydown',function(e) {
    if (e.key == 'f5') {
        window.frames['iloadframe'].location.reload(true);
        
        // Cancel full refresh (works in some browsers)
        new Event(e).stop();
        return false;
    }
});



////////////////////////////////////////////////////////////
// Parent page load
////////////////////////////////////////////////////////////
var roarqueue;
window.addEvent('domready', function() {
    // Ajaxy navigation handles
    scantags("topbox");
    $('iloadframe').addEvent('load',inlineinit);
    
    // Zoomy image bar
    var imgs = $$('#imgbar a');
    imgs.each(function(element) {
        var elementfx = new Fx.Morph(element.getElement('img'), {duration:300, wait:false});
        element.addEvent('mouseenter', function(){
            elementfx.start({'height': '64px','width':'64px','opacity':'1'});
            document.getElementById('titletext').innerHTML = element.title;
        });
        element.addEvent('mouseleave', function(){
            if (!this.hasClass('selected')) {
                elementfx.start({'height': '50px','width':'50px','opacity':'0.4'});
            }
            document.getElementById('titletext').innerHTML = defaulttitle;
        });
        element.fireEvent('mouseleave');
    });
    
    // Rounded corners
    Nifty("#contentbox","big transparent bottom");
    //Nifty("#topbar","big transparent top");
    Nifty("h1","big transparent top");
    
    // Initialize Roar
    roarqueue = new Roar({duration:5000,position:'bottomRight',margin:{x:40,y:10}});
    
    // Internet Explorer rantings
    if ( (window.ie) && (!window.ie7) ) {
        var ie = new Element('div', {'class': 'warning'});
        ie.set('html',"<h3>Internet Explorer</h3><p><?php echo get_lang('admin','ie6',false,true); ?></p>");
        ie.injectBefore($('imgbar'));
    }
});



////////////////////////////////////////////////////////////
// Functions to run first load of a logged in user
////////////////////////////////////////////////////////////
var loggedinloadp = false;
var ujson = false;
function loggedinload(superallow,updatejson) {
    if (superallow) {
        $('superallow').setStyle('display','inline');
    } else {
        $('superallow').setStyle('display','none');
    }
    
    
    // Once only
    if (loggedinloadp) return;
    loggedinloadp = true;
    
    // Show hidden buttons
    $('logoutlink').setStyle('visibility','visible');
    
    // Ajaxy update checker
    if (updatejson) {
        $('connectionstatus').setStyle('display','inline');
        ujson = new Request.JSON({url:"index.php", onComplete:function(response){
            if (response) {
                //$('connectionstatus').setAttribute('title',"<?php echo get_lang('admin','connected',false,true); ?>");
                //$('connectionstatus').getElement('img').setAttribute('src','img/crystal/wizard.png');
                $('connectionstatus').setStyle('display','none');
                $('connectingbox').getElement('p').set('html',"<?php echo get_lang('admin','connectedex',false,true); ?>");
                
                if (response.update) {
                    var update = new Element('div', {'class': 'goodmsg'});
                    update.set('html',"<?php echo get_lang('admin','update',false,true); ?>");
                    update.injectBefore($('admin_title'));
                    manage_messages();
                    scantags('topbox');
                    show_msg(1);
                }
                ujson = false;
            } else {
                connectfail();
            }
        }}).get({type:'json',p:'special:updatecheck'});
        
        // Timeout of 15 seconds
        (function connecttimeout() {
            if (ujson) {
                ujson.cancel();
                ujson = false;
                connectfail();
            }
        }).delay(15000);
    }
    
}
function connectfail() {
    $('connectionstatus').setAttribute('title',"<?php echo get_lang('admin','connectfail',false,true); ?>");
    $('connectionstatus').getElement('img').setAttribute('src','img/connectfail.png');
    $('connectingbox').getElement('p').set('html',"<?php echo get_lang('admin','connectfailex',false,true); ?>");
}
function showconnecting() {$('connectingbox').setStyle('display','block');}
function hideconnecting() {$('connectingbox').setStyle('display','none');}


var superuserrefresh = false;
function issuperuser() {
    $('superuserstatus').setStyle('display','inline');
    if (!superuserrefresh) {
        var func = function () {reload_img("index.php?p=special:refresh_super&type=ajax");};
        superuserrefresh = func.periodical(1200000);
    }
}
function isnotsuperuser() {
    $('superuserstatus').setStyle('display','none');
    if (superuserrefresh) superuserrefresh = $clear(superuserrefresh);
}
function showsuperuser() {$('superuserbox').setStyle('display','block');}
function hidesuperuser() {$('superuserbox').setStyle('display','none');}



////////////////////////////////////////////////////////////
// Functions to run every page
////////////////////////////////////////////////////////////
function everyload() {
    // Reset message show/hide
    $('msg_hold').set('html','');
    manage_messages();
    
    // Rounded corners
    Nifty("#userbar","br");
    Nifty("#locationstore","bl");
    Nifty("#inlinepage label","top transparent");
    
    // Advanced
    $$('.resizeTextarea').each(function (element) {resizeTextarea(element);});
    $$('.icheckbox').each(function (element) {icheckbox(element);});
}



////////////////////////////////////////////////////////////
// Ajaxy structure
////////////////////////////////////////////////////////////
var defaulttitle;
var section;
var loadfade = false;
function inlineclose() {
    // Set title as loading
    $('titletext').set('text','<?php echo get_lang('admin','loading'); ?>');
    defaulttitle = '<?php echo get_lang('admin','loading'); ?>';
    //loadfade = new Fx.Style($('inlinepage'),'opacity',{duration:6000, wait:false, transition: Fx.Transitions.Sine.easeInOut}).start(0);
}
function inlineinit() {
    // Can't use $$ because mootools doesn't exist in that frame!
    var innerhtml = window.frames['iloadframe'].document.getElementsByTagName('body')[0].innerHTML;
    
    if ($("section_" + section)) {
        $("section_" + section).removeClass('selected');
        $("section_" + section).fireEvent('mouseleave');
    }
    $("inlinepage").removeClass("section_"+section);
    
    // Set title & section
    if (window.frames['iloadframe'].document.title) {
        defaulttitle = window.frames['iloadframe'].document.title;
        if (defaulttitle.indexOf('::')>0) {
            section = defaulttitle.substr(0,defaulttitle.indexOf('::'));
            if ($("section_" + section)) {
                $("section_" + section).addClass('selected');
                $("section_" + section).fireEvent('mouseenter');
            }
            $("inlinepage").addClass("section_"+section);
            
            defaulttitle = defaulttitle.substr(defaulttitle.indexOf('::')+2);
            document.title = "ComicCMS :: "+defaulttitle;
        }
        $('titletext').set('text',defaulttitle);
    } else {
        $('titletext').set('text','Untitled Document');
        defaulttitle = 'Untitled Document';
    }
    
    // Extract javascript
    var exec = '';
    if (innerhtml.indexOf('__ComicCMS javascript__')>0) {
        var start = innerhtml.indexOf('__ComicCMS javascript__') + 24;
        var end = innerhtml.indexOf('ComicCMS javascript//-->') - start - 1;
        exec = innerhtml.substr(start,end);
        innerhtml = innerhtml.substr(0,innerhtml.indexOf('__ComicCMS javascript__'));
    }
    
    // Extract full errors
    if (innerhtml.indexOf('<!--ComicCMS_Body//-->')>0) {
        var error = innerhtml.substr(0,innerhtml.indexOf('<!--ComicCMS_Body//-->'));
        innerhtml = innerhtml.substr(innerhtml.indexOf('<!--ComicCMS_Body//-->')+22);
        innerhtml = innerhtml.substr(0,innerhtml.indexOf('<!--ComicCMS_Page')) + "<div class=\"errmsg\"><h3>ComicCMS Error!</h3>" + error + "<p>You should <strong>never</strong> receive one of these errors, please report it at the <a href=\"http://comiccms.com/forum/\" target=\"_blank\">ComicCMS forums</a>.</p></div>" + innerhtml.substr(innerhtml.indexOf('<!--ComicCMS_Page')+17);
    } else {
        innerhtml = innerhtml.replace(/<!--ComicCMS_Page/,"");
    }
    
    // Can't use setHTML because mootools doesn't exist in that frame!
    window.frames['iloadframe'].document.getElementsByTagName('body')[0].innerHTML = 'iframe cleared';
    
    // Set page
    if (loadfade) loadfade.stop();
    $('inlinepage').setStyle('opacity',1);
    $('inlinepage').set('html',innerhtml);
    everyload();
    if (exec) eval(exec);
    scantags();
}



////////////////////////////////////////////////////////////
// Link & form supressor
////////////////////////////////////////////////////////////
function scantags(box) {
    if (!box) box = 'contentbox';
    
    // Set hyperlinks
    $$('#' + box + ' a').addEvent('click',function (evt) {
        if ( (!this.getAttribute('target')) && (this.href.indexOf('/admin/')>0) ) {
            new Event(evt).stop(); // Moo fix until 1.2
            var url = this.href;
            if (url.indexOf('?') >= 0) {
                url = url.substr(0,url.indexOf('?')) + "?type=inline&" + url.substr(url.indexOf('?')+1);
            } else {
                url = url + "?type=inline";
            }
            inlineclose();
            window.frames['iloadframe'].location.href = url;
            return false;
        }
    });
    
    // Set forms
    $$('#' + box + ' form').each(function (element) {
        if (!element.getAttribute('target')) {
            if (element.getAttribute('method') == "post") {
                var url = element.getAttribute('action');
                if (url.indexOf('?') >= 0) {
                    url = url.substr(0,url.indexOf('?')) + "?type=inline&" + url.substr(url.indexOf('?')+1);
                } else {
                    url = url + "?type=inline";
                }
                element.setAttribute('action',url);
            } else {
                var get = document.createElement('input');
                get.setAttribute('value','inline');
                get.setAttribute('name','type');
                get.setAttribute('type','hidden');
                element.appendChild(get);
            }
            element.addEvent('submit',inlineclose);
            element.setAttribute('target','iloadframe');
        }
    });
}



////////////////////////////////////////////////////////////
// Index errors, warnings and messages as hideable tabs
////////////////////////////////////////////////////////////
function manage_messages() {
    var hidelink;
    var aid=0;
    var autohide = new Array();
    var x=1;
    $$('.errmsg,.goodmsg,.warning,.debug').each(function (msg) {
        if (!msg.getAttribute('id')) {
            while ($('msg_' + x)) x++;
            msg.setAttribute('id','msg_' + x);
            hidelink = document.createElement('a');
            hidelink.setAttribute('class','hidelink');
            hidelink.set('html','<?php echo get_lang('admin','hide'); ?>');
            hidelink.setAttribute('href','javascript:hide_msg(' + x + ');');
            
            if (msg.getAttribute('class') == 'goodmsg') {
                roarqueue.alert("Hurray!",msg.get('text'));
            }
            
            if (msg.getElementsByTagName('h3')[0]) {
                msg.getElementsByTagName('h3')[0].appendChild(hidelink);
            } else {
                msg.appendChild(hidelink);
            }
            
            if ( (msg.getAttribute('class') == 'debug') || (msg.getAttribute('class') == 'goodmsg') ) {
                autohide[aid] = x;
                aid++;
            }
        }
        x++;
    });
    for (x=0;x<autohide.length;x++) {
        hide_msg(autohide[x],true);
    }
}
function show_msg(id) {
    var tab = $('msg_s_' + id);
    $('msg_hold').removeChild(tab);
    
    slideopen($('msg_' + id),600);
}
function hide_msg(id,skipfade) {
    var msg = $('msg_' + id);
    
    var a = document.createElement('a');
    a.setAttribute('id','msg_s_' + id);
    a.setAttribute('class','t_' + msg.getAttribute('class'));
    a.setAttribute('href','javascript:show_msg(' + id + ');');
    a.set('html',msg.getAttribute('class').substr(0,1).toUpperCase());
    $('msg_hold').appendChild(a);
    
    if (skipfade) {
        msg.setStyle('display','none');
    } else {
        slideclose(msg,800);
    }
}










////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// Misc functions //////////////////////////////////////



////////////////////////////////////////////////////////////
// Return current height value of object
////////////////////////////////////////////////////////////
function cssheight(obj) {
    if (obj.getStyle('display')=='block') return obj.getScrollSize().y;
    
    // Needs temporary display
    var defaults = obj.getStyles('position','visibility','height','display');
    obj.setStyles({
        'position':'absolute',
        'visibility':'hidden',
        'height':'auto',
        'display':'block'
    });
    var guess = obj.getScrollSize().y;
    obj.setStyles(defaults);
    return guess;
}



////////////////////////////////////////////////////////////
// Slide effect
////////////////////////////////////////////////////////////
function slideopen(obj,time) {
    obj.setStyle('display','none');
    var h = cssheight(obj);
    obj.setStyles({
        'display':'block',
        'opacity':0,
        'height':0
    });
    var slide = new Fx.Morph(obj, {duration:time, wait:false, transition: Fx.Transitions.Sine.easeInOut, onComplete:function (obj) {
        obj.setStyles({
            'height': 'auto'
        });
    }});
    slide.start({
        'height': h,
        'opacity': 1
    });
}
function slideclose(obj,time) {
    obj.setStyle('height',cssheight(obj));
    var slide = new Fx.Morph(obj, {duration:time, wait:false, transition: Fx.Transitions.Sine.easeIn, onComplete:function (obj) {
        obj.setStyles({
            'display': 'none',
            'height': 'auto'
        });
    }});
    slide.start({
        'height': 0,
        'opacity': 0
    });
}



////////////////////////////////////////////////////////////
// Refresh browser image cache
////////////////////////////////////////////////////////////
reload_queue = 0;
reload_queue_a = new Array();
function reload_img(url) {
    if (url) {
        reload_queue = reload_queue + 1;
        reload_queue_a.push(url);
        if (reload_queue == 1) reload_img();
    } else if (reload_queue > 0) {
        $('preloadframe').src = reload_queue_a.shift();
        $('preloadframe').addEvent('load',function () {
            this.removeEvents('load');
            this.addEvent('load',function () {
                reload_queue = reload_queue - 1;
                this.removeEvents('load');
                reload_img();
            });
            window.frames['preloadframe'].location.reload();
        });
    }
}










////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////// Form Widgets ///////////////////////////////////////



////////////////////////////////////////////////////////////
// Form widget - Checkbox
////////////////////////////////////////////////////////////
function icheckbox(element) {
    var v = $("input_"+element.id.substr(10));
    if (v.value == 0) {
        element.setStyle("background-image","url(img/crystal/fileclose.png)");
    } else {
        element.setStyle("background-image","url(img/crystal/button_ok.png)");
    }
    element.addEvent('click',function () {do_icheckbox(this);});
}
function do_icheckbox(element) {
    var v = $("input_"+element.id.substr(10));
    if (v.value == 0) {
        v.value = 1;
        element.setStyle("background-image","url(img/crystal/button_ok.png)");
    } else {
        v.value = 0;
        element.setStyle("background-image","url(img/crystal/fileclose.png)");
    }
}



////////////////////////////////////////////////////////////
// Form widget - List
////////////////////////////////////////////////////////////
function createlistinput(div) {
    var list = div.getElement('input').value;
    var ul = div.getElement('ul');
    if (list) {
        list = list.split(',');
        list.each(function (item) {
            item = item.replace(/&c/g,",");
            item = item.replace(/&a/g,"&");
            
            addlistelement(ul,item);
        });
    }
}
function addlistelement(ul,text) {
    var li = new Element('li');
    var span = new Element('span',{'text':text});
    var a = new Element('a',{'text':'X','href':'javascript:void();','onclick':'rlistitem(this);'});
    li.adopt(span);
    li.adopt(a);
    ul.adopt(li);
}
function slistitem(ul) {
    var input = '';
    ul.getChildren('li').each(function (li) {
        var text = li.getElement('span').get('text');
        text = text.replace(/&/g,"&a");
        text = text.replace(/,/g,"&c");
        
        if (input) input = input + ',';
        input = input + text;
    });
    ul.getParent('div').getElement('input').value = input;
}
function alistitem(input) {
    var div = input.getParent('div');
    var ul = div.getElement('ul');
    if (input.value) {
        addlistelement(ul,input.value);
        input.value = '';
        slistitem(ul);
    }
}
function rlistitem(element) {
    var ul = element.getParent('ul');
    element.getParent('li').destroy();
    slistitem(ul);
}



////////////////////////////////////////////////////////////
// Form widget - Resizable textarea
////////////////////////////////////////////////////////////
function resizeTextarea(element) {
    do_resizeTextarea(element);
    element.addEvent('keyup',function () {do_resizeTextarea(this);});
    element.addEvent('mousedown',function () {do_resizeTextarea(this);});
}
function do_resizeTextarea(textarea) {
    var copy = textarea.clone().setStyles({
        'height':'auto',
        'position':'absolute',
        'opacity':0
    }).injectAfter(textarea);
    
    copy.value = textarea.value;
    var d = copy.getScrollSize();
    textarea.setStyle('height',d.y+15);
    copy.dispose();
}



////////////////////////////////////////////////////////////
// Form widget - Password with verification
////////////////////////////////////////////////////////////
var password_v_default = false;
function password_v(input,div,verify,reset) {
    
    if (verify.value == input.value) div.setStyle('display','none');
    
    password_v_default = input.value;
    if (reset) reset.addEvent('click',function (evt) {password_reset(input,div,verify);});
    
    input.addEvent('focus',function (evt) {
        if (input.value==password_v_default) {
            input.value = '';
            verify.value = '';
        }
    });
    
    input.addEvent('keyup',function (evt) {
        if (verify.value != input.value) {
            div.setStyle('display','block');
            verify.value = '';
        }
    });
    input.addEvent('blur',function (evt) {
        if (input.value == '') {
            password_reset(input,div,verify);
        } else if (verify.value != input.value) {
            div.setStyle('display','block');
            verify.value = '';
        }
    });
    
    verify.addEvent('change',function (evt) {
        if (verify.value == input.value) {
            div.setStyle('display','none');
        }
    });
}
function password_reset(input,div,verify) {
    input.value = password_v_default;
    verify.value = password_v_default;
    div.setStyle('display','none');
}



////////////////////////////////////////////////////////////
// Form widget - Timestamp
////////////////////////////////////////////////////////////
var timestamp_date = false;
var timestamp_original = false;
var timestamp_min = false;
var timestamp_max = false;
var timestamp_input = false;

function timestamp_draft(draft) {
    if (draft==2) {
        a = 'none';
        b = 'table-row';
    } else {
        a = 'table-row';
        b = 'none';
    }
    c = a;
    if (draft==0) c = 'none';
    
    $('timestamp_draft').setStyle('display',c);
    $('timestamp_up').setStyle('display',a);
    $('timestamp_time').setStyle('display',a);
    $('timestamp_down').setStyle('display',a);
    $('timestamp_nodraft').setStyle('display',b);
    $('timestamp_drafton').setStyle('display',b);
}

function set_timestamp(obj,max,min,cur,draft) {
    $('timestamp_draft').addEvent('click',function() {
        timestamp_input.value = "9999999999";
        timestamp_draft(2);
    });
    $('timestamp_nodraft').addEvent('click',function() {
        update_time(timestamp_original);
        timestamp_draft(1);
    });
    
    
    
    timestamp_input = obj;
    
    timestamp_max = max*1000;
    timestamp_min = min*1000;
    timestamp_original = cur*1000;
    
    update_time(timestamp_original);
    
    if (draft==2) {
        timestamp_input.value = "9999999999";
    }
    timestamp_draft(draft);
    
    $('year_up').addEvent('click',function () {set_time('year',true);});
    $('year_down').addEvent('click',function () {set_time('year',false);});
    $('month_up').addEvent('click',function () {set_time('month',true);});
    $('month_down').addEvent('click',function () {set_time('month',false);});
    $('day_up').addEvent('click',function () {set_time('day',true);});
    $('day_down').addEvent('click',function () {set_time('day',false);});
    $('hour_up').addEvent('click',function () {set_time('hour',true);});
    $('hour_down').addEvent('click',function () {set_time('hour',false);});
    $('minute_up').addEvent('click',function () {set_time('minute',true);});
    $('minute_down').addEvent('click',function () {set_time('minute',false);});
}
function update_time(timestamp) {
    if (timestamp) {
        // Apply restrictions
        if (timestamp < timestamp_min) timestamp = timestamp_min;
        if ( (timestamp_max>0) && (timestamp > timestamp_max) ) timestamp = timestamp_max;
        
        // Make sure the textarea is synced
        timestamp_input.value = Math.floor(timestamp/1000);
        timestamp_date = new Date(timestamp_input.value*1000);
    }
    
    var months = new Array(
        '<?php echo get_lang('date','month1'); ?>',
        '<?php echo get_lang('date','month2'); ?>',
        '<?php echo get_lang('date','month3'); ?>',
        '<?php echo get_lang('date','month4'); ?>',
        '<?php echo get_lang('date','month5'); ?>',
        '<?php echo get_lang('date','month6'); ?>',
        '<?php echo get_lang('date','month7'); ?>',
        '<?php echo get_lang('date','month8'); ?>',
        '<?php echo get_lang('date','month9'); ?>',
        '<?php echo get_lang('date','month10'); ?>',
        '<?php echo get_lang('date','month11'); ?>',
        '<?php echo get_lang('date','month12'); ?>'
    );
    
    var day = timestamp_date.getDate().toString();
    var hour = timestamp_date.getHours().toString();
    var minute = timestamp_date.getMinutes().toString();
    
    $('timestamp_year').set('text',timestamp_date.getFullYear());
    $('timestamp_month').set('text',months[timestamp_date.getMonth()]);
    $('timestamp_day').set('text',day.length==1?"0"+day:day);
    $('timestamp_hour').set('text',hour.length==1?"0"+hour:hour);
    $('timestamp_minute').set('text',minute.length==1?"0"+minute:minute);
}
function set_time(type,pos) {
    var diff = 0;
    
    switch (type) {
        case 'year':
            var year = timestamp_date.getFullYear();
            var month = timestamp_date.getMonth()+1;
            
            diff = 365;
            if (pos) {
                if ( (month > 2) && (leapyear(year+1)) ) diff = 366;
                if ( (month < 3) && (leapyear(year)) ) diff = 366;
            } else {
                if ( (month > 2) && (leapyear(year)) ) diff = 366;
                if ( (month < 3) && (leapyear(year-1)) ) diff = 366;
            }
            
            diff = 60*60*24*diff;
            break;
        
        case 'month':
            var month = timestamp_date.getMonth()+1;
            var day = timestamp_date.getDate();
            
            if (pos) {
                diff = monthdays(month) - day;
                if (day > monthdays(month+1)) day = monthdays(month+1);
                diff = diff + day;
            } else {
                diff = day;
                if (day < monthdays(month-1)) diff = diff + (monthdays(month-1)-day);
            }
            diff = 60*60*24*diff;
            break;
        
        case 'day': diff = 60*60*24; break;
        case 'hour': diff = 60*60; break;
        case 'minute': diff = 60; break;
    }
    
    if (!pos) diff = diff * -1;
    diff = diff * 1000;
    update_time(timestamp_date.getTime() + diff);
}
function leapyear(year) {
    if ((((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0))) return true;
    return false;
}
function monthdays(month) {
    if ((month==1)||(month==3)||(month==5)||(month==7)||(month==8)||(month==10)||(month==12)) return 31;
    if ((month==4)||(month==6)||(month==9)||(month==11)) return 30;
    if (month==2) {
        if (leapyear(timestamp_date.getFullYear())) {
            return 29;
        } else {
            return 28;
        }
    }
}
