<?php
/********************************************************************
//                       ComicCMS lang file
//                       ==================
//                   Copyright (C) 2007 Steve H
//                      http://ComicCMS.com/
//-------------------------------------------------------------------
//  This program is free software; you can redistribute it and/or
//  modify it under the terms of the GNU General Public License
//  as published by the Free Software Foundation; either version 3
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
class language {
    var $locale = 'en';
    var $version = 6;
    
    
    
    ////////////////////////////////////////////////////////////
    // Fatal errors
    var $error = array(
        'findfile'=>'File specified not found ({{info}})',
        'readfile'=>'File specified not readable ({{info}})',
        'writefile'=>'File specified not writeable ({{info}})',
        'deletefile'=>'File specified not deleteable ({{info}})',
        
        'finddir'=>'Directory specified not found ({{info}})',
        'readdir'=>'Directory specified not readable ({{info}})',
        'writedir'=>'Directory specified not writeable ({{info}})',
        
        'tpl_pluginbad'=>'Plugin specified does not exist ({{info}})',
        'tpl_pluginnomethod'=>'Plugin does not have the required template method ({{info}})',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Global admin
    var $admin = array(
        'mode_debug'=>'Debug Mode',
        'mode_translate'=>'Translate Mode',
        
        'nojs'=>'In order to use ComicCMS javascript is required. Please enable javascript in your browser to proceed.',
        'loading'=>'Loading...',
        'hide'=>'Hide',
        'working'=>'Working......',
        
        'error'=>'Error',
        'warning'=>'Warning',
        
        'update'=>'Updates are available for your ComicCMS installation! Please <a href="?p=config:update">upgrade as soon as possible</a>.',
        
        
        // Notifications
        'connecting'=>'Connecting...',
        'connectingex'=>'ComicCMS is connecting to ComicHQ to check for updates',
        'connected'=>'ComicCMS Connection',
        'connectedex'=>'ComicCMS is connected to ComicHQ, a service that provides you with automatic update notifications.',
        'connectfail'=>'ComicCMS Connection Failed',
        'connectfailex'=>'ComicCMS failed to connect to ComicHQ. This could be a temporary problem or a technical limitation. If ComicCMS frequently fails to connect be sure to check for updates manually.',
        'connectwhat'=>'What\'s this?',
        'superuser'=>'SuperUser Authentication',
        'superuserex'=>'You are authenticated as a SuperUser, giving you access to configuration pages. If you unauthenticate whilst viewing configuration pages you may lose your changes.',
        'superuserlogout'=>'Unauthenticate',
        'passwordwrong'=>'Incorrect Password',
        
        
        // Form widgets
        'form_badkey'=>'Form security key did not validate, please try again',
        'input_missing'=>'Form error: {{info}} input missing',
        
        'password_v'=>'Verify Password:',
        'password_reset'=>'Reset Password',
        'password_v_fail'=>'Password validation failed',
        
        'select_hax'=>'Unexpected value from select field',
        
        'timestamp_min'=>'Timestamp input smaller than allowed values',
        'timestamp_max'=>'Timestamp input greater than allowed values',
        'timestamp_draft'=>'or: Save item as draft',
        'timestamp_nodraft'=>'or: Add item at a specific time',
        'timestamp_drafton'=>'Saving item as a draft. Item will be saved in admin panel drafts but will not be added to your public website.',
        
        
        // Page errors
        'badsection'=>'Section requested not recognised',
        'badpage'=>'Page requested not recognised',
        'permbad'=>'Sorry, but the usergroup you belong to is not permitted to view that page/perform that action.',
        
        
        // Internet Explorer rant
        'ie1'=>'Sorry, looks like you\'re using Internet Explorer',
        'ie2'=>'The ComicCMS admin panel is feature rich, interactive, and rather good looking. Unfortunately Internet Explorer (the program you are using to view this site) can\'t handle that kind of website. It is a bit lengthy to explain here but long story short the admin panel just will not work in Internet Explorer.',
        'ie3'=>'<strong>Please note:</strong> This restriction from using Internet Explorer is only for the administration of your site. <strong style="font-size:1.3em; font-weight:normal;">Any person in any browser can view your public website, read your webcomic, and interact uninterrupted</strong>.',
        'ie4'=>'To use the ComicCMS admin panel please upgrade to another browser.<br />We suggest <a href="http://getfirefox.com/" target="_blank">Firefox</a>:',
        'ie5'=>'We hope you\'ll find your new browser to be faster, simpler, and more secure. We heartily suggest you stop using Internet Explorer altogether so the web can thrive as it should.',
        'ie6'=>'If you\'re not actually using Internet Explorer then please file a bug at the <a href="http://comiccms.com/forum/" target="_blank">ComicCMS forums</a> and <a href="?forceie=true">Click Here</a> to continue to the admin panel.<span style="font-size:0.8em;"> (IE users: don\'t say we didn\'t warn you)</span>',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Admin page titles
    var $title = array(
        'sectionmain'=>'General Use',
        'sectioncomic'=>'Manage Comics',
        'sectionnews'=>'Manage News',
        'sectionplugin'=>'Plugins',
        'sectiontemplate'=>'Edit Templates',
        'sectionconfig'=>'Configure ComicCMS',
        
        'speciallogin'=>'Login to ComicCMS',
        'specialsearch'=>'Search',
        'specialinstall'=>'Install ComicCMS',
        'specialeditself'=>'Edit Your User',
        'speciallogin_super'=>'Authenticate as SuperUser',
        
        'main'=>'General Use',
        'mainchangelog'=>'Changelog',
        'mainlicense'=>'License',
        'maincredits'=>'About ComicCMS',
        
        'comic'=>'Manage Comics',
        'comicadd'=>'Add Comic',
        'comicedit'=>'Edit Comic',
        'comiceditdata'=>'Edit Comic Data',
        'comiceditimg'=>'Edit Comic Image',
        'comicdelete'=>'Delete Comic',
        'comicdraft:main'=>'Comic Drafts &amp; Queued Comics',
        'comicdraft:comic'=>'Comic Drafts',
        'comicdraft:edit'=>'Use Comic Draft',
        'comicdraft:delete'=>'Delete Comic Draft',
        'comictags'=>'Manage Tags',
        'comicaddcollection'=>'Add Tag Collection',
        'comiceditcollection'=>'Edit Tag Collection',
        'comicdeletecollection'=>'Delete Tag Collection',
        
        'news'=>'Manage News',
        'newsadd'=>'Add News',
        'newsedit'=>'Edit News',
        'newseditdata'=>'Edit News Data',
        'newsdelete'=>'Delete News',
        'newsdraft:main'=>'News Drafts &amp; Queued News',
        'newsdraft:news'=>'News Drafts',
        'newsdraft:edit'=>'Use News Draft',
        'newsdraft:delete'=>'Delete News Draft',
        'newsaddcomment'=>'Add Comment',
        'newseditcomment'=>'Edit Comment',
        'newsdeletecomment'=>'Delete Comment',
        
        'plugin'=>'Manage Plugins',
        'pluginadd'=>'Install Plugin',
        'pluginuse'=>'Use Plugin',
        'pluginedit'=>'Edit Plugin',
        'plugindelete'=>'Remove Plugin',
        'pluginevent'=>'Plugin Events',
        
        'template'=>'Manage Templates',
        'templateadd'=>'Add File',
        'templateedit'=>'Edit File',
        'templatedelete'=>'Delete File',
        'templatepage:add'=>'Add Page',
        'templatepage:edit'=>'Edit Page',
        'templatepage:delete'=>'Delete Page',
        'templateeditglobal'=>'Edit Header/Footer',
        
        'config'=>'Configure ComicCMS',
        'configuser:main'=>'Manage Users',
        'configuser:add'=>'Add User',
        'configuser:edit'=>'Edit User',
        'configusergroup:main'=>'Manage Usergroups',
        'configusergroup:add'=>'Add Usergroup',
        'configusergroup:edit'=>'Edit Usergroup',
        'configusergroup:delete'=>'Delete Usergroup',
        'configcache'=>'Clear Cache',
        'configsetting'=>'Edit Settings',
        'configupdate'=>'Check for Updates',
        'configuninstall'=>'Uninstall ComicCMS',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Admin page descriptions
    var $intro = array(
        'specialeditself'=>'To attach an avatar to your user you can sign up to <a href="http://gravatar.com/signup/" target="_blank">Gravatar</a> with the same email address set here.',
        'speciallogin_super'=>'For security reasons you must enter your password to access this page, once you have done so your login will be marked as a superuser and you can access all configuration pages you have permission to until you close the ComicCMS admin panel.',
        
        'mainchangelog'=>'The Changelog is displayed below',
        'mainlicense'=>array(
            'All content copyright their respective owners. Please respect the contributers\' rights. Everything is licensed under the {{GPL}}. You are free to use and modify but please feed all improvements back into the community at {{HQ}}',
            'Below is a local copy of the GPL license, if altered in any way it is null and void, the official license can be viewed on the {{GPL}} site.',
        ),
        'maincredits'=>array(
            'ComicCMS &copy; Steve H 2007 under the {{GPL}}',
            'English translation provided by Steve H',
        ),
        
        'comicdelete'=>array(
            'Deleting a comic is irreversable, the image and data will be removed. Are you sure you want to delete this comic?',
            'And news posts will be shifted to the previous comic and can not be moved back.',
        ),
        'comicdraft:main'=>array(
            'Comic drafts are comics that havn\'t yet been published on your site. Either because they are queued to be added at a later time or because you only uploaded an image without confirming it\'s data.',
            'Editing any comic draft will automatically add it to your site unless it\'s date/time is set in the future, in which case it will be queued to be added at that time.',
        ),
        'comicdraft:delete'=>array(
            'Deleting a comic draft is irreversable. The saved data and comic image will be lost. Are you sure you want to delete this comic draft?'
        ),
        
        'newsdelete'=>array(
            'Deleting a news post is irreversable. Are you sure you want to delete this post? All news data and comments on this post will be lost',
            'Be warned. This function will delete the news item entierly, not just the link to the comic it is attached to.',
        ),
        'newsdraft:main'=>array(
            'News drafts are news posts that havn\'t yet been published on your site. Either because they are queued to be added at a later time or because you set them as drafts.',
            'Editing any news draft will automatically add it to your site unless it\'s date/time is set in the future, in which case it will be queued to be added at that time.',
        ),
        'newsdraft:delete'=>array(
            'Deleting a news draft is irreversable. Are you sure you want to delete this news draft?'
        ),
        
        'template'=>'The template system is how you show your comic to the world and is split in to two parts. Files (which are the files your pages are stored in) and Page Templates (which are the content that goes in to each page of your site). Files can hold multiple pages each being displayed depending on triggers in how the file is accessed.',
        'templateadd'=>'Adding a new file creates a new file on your website for you to fill with pages. Just enter the relative name to your base ComicCMS URL to add the file, e.g. to add http:///new.php just type in new.php (all files must end in .php).',
        'templatedelete'=>'Deleting a template file is irreversable, please confirm your action. The file will be deleted and it\'s data purged including all page templates.',
        'templatedeletepage'=>'Deleting a page template is irreversable',
        
        'config'=>'Here you can edit ComicCMS\' various settings, tweak performance and manage user access.',
        'configuser:edit'=>'To attach an avatar to this user they can sign up to <a href="http://gravatar.com/signup/" target="_blank">Gravatar</a> with the same email address set here.',
        'configusergroup:delete'=>'Are you sure you want to delete this usergroup? The usergroup will be lost permanently and the users it holds will be moved to another usergroup.',
        'configcache'=>array(
            'Here you can manualy regenerate the cache files ComicCMS uses to serve your website\'s pages. Please note that most of the time you do not need to use this feature, ComicCMS will auto-clear the neccisary caches when you add/edit comics, news and templates.',
            'You may need to submit this form if you are making manual changes to your ComicCMS system or performing manual backups.',
        ),
        'configupdate'=>'Updating ComicCMS is a very important thing to do, luckily we\'ve made it super-easy! If you choose not to install an update you will not be notified of it again but can come back to this page to install it later.',
        'configuninstall'=>'Because of ComicCMS\' grasp on files some servers may deny you access to the ComicCMS source via FTP. Selecting to uninstall ComicCMS will release all file and folder permissions allowing you to delete ComicCMS through FTP.',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Special pages
    var $special = array(
        // Login
        'login'=>'Login',
        'login_username'=>'Username',
        'login_password'=>'Password',
        'usernamemissing'=>'Username missing',
        'usernamewrong'=>'Invalid username',
        'userpassmissing'=>'Password missing',
        'passwordwrong'=>'Incorrect password',
        'login_repost'=>'It looks like you were trying to submit something when your login failed, do you want to automatically resubmit that data?',
        'login_repost_yes'=>'Yes please!',
        'login_repost_no'=>'No thanks',
        
        // Install
        'installdone'=>'All set! Seriously, that\'s it!<br />You can now start using ComicCMS to your heart\'s content.<br /><br />Login with the user you just created with the form below.',
        'installlock'=>'For security reasons installation is locked down to the original user only. To unlock this file FTP to your site and delete lock.php from this folder.',
        'installcreate'=>'Please create your first user',
        'installcontinue'=>'Continue',
        'installusername'=>'Username',
        'installpassword'=>'Password',
        'installpasswordv'=>'Verify Password',
        
        // Edit self
        'input_name'=>'Your username',
        'input_password'=>'Your password',
        'input_email'=>'Your email address',
        
        // Login as SuperUser
        'superlogingood'=>'Authenticated as SuperUser',
        'usergroupnosuper'=>'The usergroup you are a part of does not have permission to log in as a SuperUser',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Main pages
    var $main = array(
        // Main Page
        'comicquicklinks'=>'Comic Quicklinks',
        'newsquicklinks'=>'News Quicklinks',
        'mainpage1'=>'Welcome to ComicCMS. This is your admin panel where you can do all kinds of things to your webcomic\'s existance.',
        'helptitle'=>'Help &amp; Support',
        'help1'=>'If you ever have any problems or questions then run over to the FAQs and help forums at {{HQ}}.',
        'help2'=>'Happy comicing!',
        
        'nopanel'=>'The usergroup you are a member of does not allow you to use the admin panel. So you can only access your user editing form or <a href="?type=logout" target="_top">log out</a>.',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Comic pages
    var $comic = array(
        // Mesages
        'imgadd'=>'Your comic image has been uploaded successfuly but will not be shown on your website until you go through this page. Please add any more details you would like and click "Add Comic"',
        'add_good'=>'Comic Added Successfuly',
        'cron_good'=>'Comic has been queued for addition. The comic will appear on your site at the time you gave',
        'edit_good'=>'Comic Edited Successfuly',
        'draft_good'=>'Comic added as draft',
        'delete_good'=>'Comic Deleted Successfuly',
        
        // Form
        'input_title'=>'Comic Title',
        'input_blurb'=>'Comic Blurb',
        'input_tagline'=>'Comic Tagline',
        'input_comicimg'=>'Comic Image',
        'input_timestamp'=>'Comic Time/Date',
        'input_tag'=>'Comic Tags',
        'comicimage'=>'First, upload the comic image',
        'uploadcomic'=>'Upload Comic',
        
        // Errors
        'comicnoid'=>'No comic ID set, please choose a comic from the list below',
        'comicbadid'=>'The requested comic could not be found. Please try again from the list below',
        'noimg'=>'No comic file was uploaded',
        'noext'=>'No file extension detected',
        'badext'=>'Extension of uploaded file is not allowed ({{info}})',
        'comicimgfail'=>'Either your server or your browser does not recognize the file type {{info}} as an image',
        
        // General
        'comicid'=>'Comic #',
        'untitled'=>'Untitled comic',
        
        'addflag'=>'Click up there to add your first comic!',
        'nocomics'=>'Hello, welcome to ComicCMS. You haven\'t got any comics yet so you should probably get around to uploading some. You can do that by clicking the link above or using the comic upload form on the main admin page.',
        
        'prevcomic'=>'Previous, Older Comic',
        'nextcomic'=>'Next, Newer Comic',
        'nextoffset'=>'Next {{info}} older comics',
        'prevoffset'=>'{{info}} more recent comics',
        'switchthumb'=>'Switch to thumbnail view',
        'switchlist'=>'Switch to list view',
        
        'draft'=>'Draft Comic',
        'drafts'=>'Draft Comics',
        'queued'=>'Queued Comics',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // News pages
    var $news = array(
        // Messages
        'add_good'=>'News Added',
        'cron_good'=>'News has been queued for addition successfuly. The news will appear at the time you gave.',
        'edit_good'=>'News Edited',
        'draft_good'=>'News added as draft',
        'delete_good'=>'News Deleted',
        
        // Form
        'input_title'=>'News Title',
        'input_post'=>'News Content',
        'input_timestamp'=>'News Date/Time',
        
        // Errors
        'newsnoid'=>'No news ID set, please choose a news post from the list below',
        'newsbadid'=>'The requested news post could not be found. Please try again from the list below',
        'newsnocomic'=>'Could not retrieve most recent comic to link post to',
        'short_post'=>'News post not long enough',
        
        // General
        'addflag'=>'Click up there to add your first news post!',
        'nonews'=>'Looks like you haven\'t got any news posts, you should start writing some. News posts are a great way to tell you viewers about your life, other comics, general points of interest, etc. ComicCMS will link your news post to the comics it\'s added around the same time as, so people browsing your archives can still read them.',
        
        'untitled'=>'Untitled News',
        'nextoffset'=>'Next {{info}} older news posts',
        'prevoffset'=>'{{info}} more recent news posts',
        'nextnews'=>'Newer News',
        'prevnews'=>'Older News',
        
        'draft'=>'Draft',
        'drafts'=>'Draft News',
        'queued'=>'Queued News',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Plugin pages
    var $plugin = array(
        // Messages
        'add_good'=>'Plugin Installed',
        'editdone'=>'Plugin Settings Edited',
        'deletedone'=>'Plugin Uninstalled',
        
        // Form
        'input_plugin'=>'Plugin file',
        'input_fullremove'=>'Remove all settings &amp; data associated with this plugin',
        
        // Errors
        'pluginnoid'=>'No plugin ID set, please choose a plugin from the list below',
        'pluginbadid'=>'The requested plugin could not be found. Please try again from the list below',
        'nofile'=>'No plugin file uploaded',
        'phperr'=>'Plugin file was not a .php file',
        'noext'=>'Plugin class does not extend ComicCMS. Change the decleration to class pl_{{info}} extends ComicCMS {',
        'vererr'=>'Plugin is not compatable with this version of ComicCMS',
        'exists'=>'A plugin by this name already exists',
        'pluginsettype'=>'Setting type given invalid ({{info}})',
        'pluginshortinp'=>'Input too short ({{info}})',
        'pluginlonginp'=>'Input too long ({{info}})',
        'eventbad'=>'Event not recognized - {{info}}',
        'plugindie'=>'Plugin failed to initialize - {{info}}',
        'eventoverflow'=>'Too many vars in event - {{info}}',
        
        // General
        'warning'=>'WARNING - upload files from only the most trusted of sources! Plugins have full reign over ComicCMS including all of your website data.',
        
        'pluginauthor'=>'Plugin designed by',
        'pluginnouse'=>'Plugin has no usage page',
        'pluginnoedit'=>'Plugin has no editable settings',
        'pluginnodelete'=>'Plugin has no deletion page',
        
        'noeditp'=>'There is no edit process function',
        'nodelete'=>'Plugin does not include deletion instructions. Deleting this plugin is not recommended and could result in system instability. Are you sure you want to continue?',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Template pages
    var $file = array(
        // Messages
        'add_good'=>'File Added',
        'edit_good'=>'File Triggers Edited',
        'delete_good'=>'File Deleted',
        'pageleft'=>'Cannot delete file because page templates still exist for this file. Delete all housed page templates to continue.',
    );
    var $template = array(
        // Messages
        'edit_globals_good'=>'Global templates Edited',
        
        // Form
        'input_name'=>'File name',
        'input_varlist'=>'Global variables',
        
        'templatehvar'=>'Header Values',
        'templatefvar'=>'Footer Values',
        'templatelabel'=>'Template',
        'templatecontent'=>'ADVANCED: Content type',
        'templatefull'=>'Include header &amp; footer?',
        'templateheader'=>'Header template',
        'templatefooter'=>'Footer template',
        
        // Error
        'filenoid'=>'No file ID set, please choose a file from the list below',
        'filebadid'=>'The requested file could not be found. Please try again from the list below',
        'pagenoid'=>'No page ID set, please choose a file/page from the list below',
        'pagebadid'=>'The requested page could not be found. Please try again from the list below',
        'badpath'=>'Destination of file is outside of the ComicCMS sandbox',
        'badname'=>'File inludes reserved names, please try another name',
        'baddir'=>'Destination of file is not permitted. Files may only site in the base directory, inc folder and img folder.',
        
        // General
        'triggeredit'=>'Edit template triggers',
        'triggerdesc'=>'This section is for ADVANCED USERS ONLY. Input the trigger code for each template in these boxed and re-arrange the trigger order with the sortable to the right. A blank trigger matches everything.',
        'orderedit'=>'Edit Trigger Order',
        'orderdesc'=>'When viewing a page the template used is decided by going through the below list (top to bottom) until a trigger is matched. Make sure to put blank triggers at the bottom of the list. Re-order this list by dragging the crosshairs.',
        
        'globalseperator'=>'This is where each page of your site will appear if it is set to include the header/footer.',
        'globalvars'=>'Global variables',
        'globalvardefault'=>'Default values',
        'globalvarlist'=>'Add/remove global variables with the list above and click "Save and reload" to change the default values',
        'savereturn'=>'Save changes and reload this page',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Page template pages
    var $page = array(
        // Messages
        'add_good'=>'Template page added',
        'edit_good'=>'Template page edited',
        'delete_good'=>'Template page deleted',
        
        // Form
        'input_name'=>'Friendly name',
        'input_type'=>'Type of page',
        'input_content'=>'Page template',
        // Form
        'templatehtml'=>'HTML (normal page)',
        'templatejs'=>'Javascript',
        'templatecss'=>'CSS',
        'templaterss'=>'RSS',
        
        // General
        'savereturn'=>'Save changes and reload this page',
        'globalvars'=>'Global variables',
        'globalvarsintro'=>'Global variables are used to pass data from the page templates to the header/footer. To add more global variables go to the bottom of the Edit Header/Footer page.',
    );
    
    
    
    
    ////////////////////////////////////////////////////////////
    // Config pages
    var $config = array(
        // Messages
        'cache_good'=>'Cache cleared successfuly',
        'settings_good'=>'Settings Edited Successfuly, changes will be applied on the next page you load',
        'uninstall_good'=>'ComicCMS uninstall successful, you can now delete all files through FTP. Have a nice day',
        
        // Form
        'input_pagecache'=>'Remove all page caches',
        'input_plugincache'=>'Remove all plugin caches',
        'input_password'=>'Confirm with your password',
        
        // Error
        'uninstallbadpass'=>'Password incorrect',
        
        // General
        'noupdates'=>'No updates were found for your ComicCMS installation.',
        
        'requiredupdates'=>'Required Updates',
        'optionalupdates'=>'Optional Updates',
        'applyupdates'=>'Apply Updates',
        'updatebegin'=>'For each step of the update you must click the apply button below. Please do not interrupt the upgrade process once begun.',
        'updatedownloaded'=>'Update downloaded, continue to install this update',
        'updateinstalled'=>'Update installed, continue to download next update',
        'updatedone'=>'Update completed',
        'updatedownloaderror'=>'There was an error downloading the update files, please try again later.',
        
        'updateversion'=>'A new version of ComicCMS has been released, please set aside 10-15mins of your time before beginning the upgrade process. To begin, click the button below and follow the instructions given. Do not leave the upgrade process once begun!',
        'uninstallwarn'=>'Only submit this form if you are sure you want to delete ComicCMS! This action is irreversable and may leave your ComicCMS system open to attack.',
    );
    
    
    ////////////////////////////////////////////////////////////
    // Config:User pages
    var $user = array(
        // Messages
        'add_good'=>'User added successfully',
        'edit_good'=>'User edited successfully',
        
        // Form
        'input_name'=>'Username',
        'input_password'=>'User\'s Password',
        'input_email'=>'User\'s email address',
        'input_group'=>'Usergroup',
        
        // Error
        'badid'=>'User ID not recognized',
        'short_name'=>'Username too short',
        'dup_name'=>'Username already taken',
        'short_password'=>'Password too short',
    );
    
    
    ////////////////////////////////////////////////////////////
    // Config:Usergroup pages
    var $usergroup = array(
        // Messages
        'add_good'=>'Usergroup added successfully',
        'edit_good'=>'Usergroup edited successfully',
        'delete_good'=>'Usergroup deleted successfully',
        
        // Form
        'input_name'=>'Name',
        'input_copy'=>'Copy default permissions from:',
        'input_move'=>'Move orphaned users to:',
        'permissions'=>'Usergroup\'s Permissions',
        
        // Error
        'badid'=>'Usergroup ID not recognized',
        'short_name'=>'Name too short',
        'dup_name'=>'Name already taken',
        'same_move'=>'Usergroup to move to is the same as usergroup to be deleted',
    );
    
    
    ////////////////////////////////////////////////////////////
    // Permission names
    var $permission = array(
        'panel'=>'Access Admin Panel',
        '_superuser'=>'Authenticate as superuser',
        '_checkupdate'=>'Be notified of updates',
        
        'configuser:delete'=>'Delete User (unused)',
    );
    
    
    ////////////////////////////////////////////////////////////
    // Setting names
    var $settings = array(
        'comicexts'=>'File extensions allowed for comics',
        'allowphp'=>'Whether to allow PHP in templates',
        'allowutf8'=>'Whether to allow UTF-8/high-ASCII characters in comic data (which can break RSS readers)',
    );
    
    
    ////////////////////////////////////////////////////////////
    // Date/Time
    var $date = array(
        // Date formats
        'short_date'=>'d.m.y',
        'short_both'=>'d/m/y :: H:i',
        'long_both'=>'d.m.Y :: H:i',
        'js_both'=>'d M Y :: H:i',
        'med_date'=>'M d',
        'cur_time'=>'H:i, jM y',
        'time'=>'H:i',
        
        // Titles
        'year'=>'Year',
        'month'=>'Month',
        'day'=>'Day',
        'hour'=>'Hour',
        'minute'=>'Minute',
        
        // Weekday names
        'week0'=>'Sunday',
        'week1'=>'Monday',
        'week2'=>'Tuesday',
        'week3'=>'Wednesday',
        'week4'=>'Thursday',
        'week5'=>'Friday',
        'week6'=>'Saturday',
        
        // Month names
        'month1'=>'January',
        'month2'=>'Febuary',
        'month3'=>'March',
        'month4'=>'April',
        'month5'=>'May',
        'month6'=>'June',
        'month7'=>'July',
        'month8'=>'August',
        'month9'=>'September',
        'month10'=>'October',
        'month11'=>'November',
        'month12'=>'December',
    );
    
    
    
    ////////////////////////////////////////////////////////////
    // Action messages
    var $action = array(
        'noaction'=>'No action specified',
        'badaction'=>'Action specified invalid',
    );
    
}
?>
