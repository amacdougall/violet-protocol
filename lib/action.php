<?php

if (isset($_GET['a'])) {
    if ($class = load_plugin($_GET['a'])) {
        $method = 'a_' . $_GET['a'];
        if (method_exists($class,$method)) {
            $class->$method(array_merge($_GET,$_POST));
        } else action_display('<h2>400 Bad Request</h2>' . "\n" . '<p>' . get_lang('action','badaction') . '</p>');
    } else action_display('<h2>400 Bad Request</h2>' . "\n" . '<p>' . get_lang('action','badaction') . '</p>');
} else action_display('<h2>400 Bad Request</h2>' . "\n" . '<p>' . get_lang('action','noaction') . '</p>');

function action_display($text) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>ComicCMS</title>
  <meta name="generator" content="ComicCMS" />
</head>
<body>
<h1>ComicCMS</h1>
<?php echo str_replace('http:///',get_config('baseurl'),$text); ?>
</body>
</html>
<?php
}
?>