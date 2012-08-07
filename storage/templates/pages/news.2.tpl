text/html|1|Gid>0|N|1|Gid|
a:1:{s:5:"title";s:64:"News :: {{if:news_title}}{{news_title}}{{else}}Untitled{{endif}}";}|
<h2>{{if:news_title}}{{news_title}}{{else}}Untitled News{{endif}}</h2>
{{if:news_author_email}}<img src="http://www.gravatar.com/avatar/{{news_author_email:md5}}?s=55" alt="Gravatar" title="Posted by {{news_author_name}}" class="newsavatar" />{{endif}}
<span class="newsdetail">Posted {{news_hour}}:{{news_minute}}<br />{{news_weekday:shortname}} {{news_day}} {{news_month:name}}<br /> by {{news_author_name}}</span>
{{news_post:rich}}

<div class="navbar" style="margin-top:3em;">
    {{if:prev_news}}
        <a href="http:///news.php?id={{prev_news_id}}">&laquo; {{prev_news_title}}</a>
    {{endif}}
    {{if:next_news}}
        | <a href="http:///news.php?id={{next_news_id}}">{{next_news_title}} &raquo;</a>
    {{endif}}
</div>
