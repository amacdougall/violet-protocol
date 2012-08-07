application/rss+xml|0||C|B|0,-9,|
a:0:{}|
<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <title>Comic RSS Feed</title>
    <link>http:///</link>
    <description>RSS feed for my webcomic. Powered by ComicCMS</description>
    <pubDate>{{cache_timestamp:rfc}}</pubDate>
    <lastBuildDate>{{cache_timestamp:rfc}}</lastBuildDate>
    <generator>ComicCMS</generator>
    
    {{foreach:comic}}
    <item>
      <title>{{comic_title:xml}}</title>
      <link>http:///?id={{comic_id}}</link>
      <guid isPermaLink="true">http:///?id={{comic_id}}</guid>
      <pubDate>{{comic_timestamp:rfc}}</pubDate>
      <description>
        &lt;a href=&quot;http:///?id={{comic_id:xml}}&quot;&gt;&lt;img src="{{comic_src:xml}}" alt="Comic #{{comic_id:xml}}" title="{{comic_tagline:xml}}" /&gt;&lt;/a&gt;
        {{comic_blurb:rich:xml}}
      </description>
    </item>
    {{endeach:comic}}
  </channel>
</rss>
