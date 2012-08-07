application/rss+xml|0||N|B|0,-9,|
a:0:{}|
<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <title>Comic News RSS Feed</title>
    <link>http:///</link>
    <description>RSS feed for my comic news. Powered by ComicCMS</description>
    <pubDate>{{cache_timestamp:rfc}}</pubDate>
    <lastBuildDate>{{cache_timestamp:rfc}}</lastBuildDate>
    <generator>ComicCMS</generator>
    
    {{foreach:news}}
    <item>
      <title>{{news_title:xml}}</title>
      <link>http:///news.php?id={{news_id:xml}}</link>
      <guid isPermaLink="true">http:///news.php?id={{news_id:xml}}</guid>
      <pubDate>{{news_timestamp:rfc}}</pubDate>
      <description>
        {{news_post:rich:xml}}
      </description>
    </item>
    {{endeach:news}}
  </channel>
</rss>
