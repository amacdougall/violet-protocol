text/html|1||N|R|0,1|
a:1:{s:5:"title";s:12:"News Archive";}|
<h2>News Archive</h2>
<ul>
{{foreach:news}}    <li><a href="http:///news.php?id={{news_id}}">{{if:news_title}}{{news_title}}{{else}}Untitled News{{endif}}</a></li>{{endeach:news}}
</ul>
