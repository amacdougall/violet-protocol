text/html|1||C|1||
a:1:{s:5:"title";s:64:"{{if:comic_title}}{{comic_title}}{{else}}Untitled Comic{{endif}}";}|
{{plugin:dropdownform}}
<div id="comicset">
    <div class="navbar">
        {{if:prev_comic}}
            <a href="http:///?id={{first_comic_id}}" title="{{first_comic_title}}">First</a>
            | <a href="http:///?id={{prev_comic_id}}" title="{{prev_comic_title}}">Prev</a>
            | Next | Last
        {{else}}
            Add more comics to get a navigation bar here
        {{endif}}
    </div>
    
    <h2>{{if:comic_title}}{{comic_title}}{{else}}Untitled Comic{{endif}}</h2>
    <img src="{{comic_src}}" alt="Comic #{{comic_id}}" id="comicimg" title="{{comic_tagline}}" />

    <div class="navbar">
        {{if:prev_comic}}
            <a href="http:///?id={{first_comic_id}}" title="{{first_comic_title}}">First</a>
            | <a href="http:///?id={{prev_comic_id}}" title="{{prev_comic_title}}">Prev</a>
            | Next | Last
        {{else}}
            Add more comics to get a navigation bar here
        {{endif}}
    </div>
</div>




<div id="sidenav">
  <h3>Navigation</h3>
  <ul>
    <li><a href="http:///archive.php">Comic Archive</a></li>
    <li><a href="http:///news.php">News Archive</a></li>
    <li><a href="http:///inc/feed.php">Comic RSS</a></li>
  </ul>
  
  <h3>Links</h3>
  <ul>
    <li><a href="http://comiccms.com/">ComicCMS</a></li>
    <li><a href="http://comiccms.com/docs/">Help/Docs/FAQ</a></li>
    <li><a href="http://comiccms.com/forum/">Help Forum</a></li>
    <li><a href="http://comicrank.com/">Comic Rank</a></li>
  </ul>
</div>




<div id="mainlower">
  <div id="comicblurb">
    {{if:comic_blurb}}<h3>Author's Comment</h3>{{endif}}
    {{comic_blurb:rich}}
    <span style="display:block; text-align:right; font-weight:bold; font-size:12px;">Uploaded by {{comic_author_name}} at {{comic_hour}}:{{comic_minute}} on {{comic_day}} {{comic_month:name}}</span>
  </div>

  {{if:comic_news}}
    <h2>News</h2>
  {{else}}
    <h2>No News</h2>
    <p>Add some news posts and news linked to the current comic will show up here.</p>
  {{endif}}
  {{foreach:comic_news:reverse}}
  <div class="newspost">
      <h3><a href="http:///news.php?id={{news_id}}">{{if:news_title}}{{news_title}}{{else}}Untitled{{endif}}</a></h3>
      {{if:news_author_email}}<img src="http://www.gravatar.com/avatar/{{news_author_email:md5}}?s=55" alt="Gravatar" title="Posted by {{news_author_name}}" class="newsavatar" />{{endif}}
      <span class="newsdetail">Posted {{news_hour}}:{{news_minute}}<br />{{news_weekday:shortname}} {{news_day}} {{news_month:name}}<br /> by {{news_author_name}}</span>
      {{news_post:rich}}
      <div class="newslinks"><a href="http:///news.php?id={{news_id}}">Permalink</a> | <a href="http:///inc/feed.php?feed=news">News RSS feed</a></div>
  </div>
  {{endeach:comic_news}}
</div>
