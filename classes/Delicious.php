<?php

class Delicious extends API {  
  function get_bookmarks_for_item($q){
    if (!$uri = $q['uri'])
      return FALSE;

    $json = get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));
    //debug($json);
    
    if (!is_array($json) || empty($json))
      return FALSE;

    $meta = array('total' => $json[0]->total_posts, 'tags' => $json[0]->top_tags);
    
    $dom = get_data('http://feeds.delicious.com/v2/rss/url/' . md5($uri), array(), 'dom');
    //debug(simplexml_import_dom($dom));
  
    $items = array();
    if (is_object($dom)){
      $xpath = new DOMXPath($dom);
      foreach ($xpath->query('channel/item') as $node)
        $items[] = array(
          'user' => $node->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator')->item(0)->nodeValue,
          'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
          'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
          'tags' => $node->getElementsByTagName('category')->item(0)->nodeValue,
          );
    }

    return array($items, $meta);
  }
}
