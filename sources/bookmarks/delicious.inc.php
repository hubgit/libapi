<?php

# 

function bookmarks_delicious($q){
  if (!$uri = $q['uri'])
    return FALSE;
    
  $json = get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));
  
  debug($json);
    
  if (!is_array($json) || empty($json))
    return FALSE;
    
  $result = $json[0];
  $output = array($result->total_posts, array(), array('tags' => (array) $result->top_tags));
  
  $xml = get_data('http://feeds.delicious.com/v2/rss/url/' . md5($uri), array(), 'xml');
  if (!is_object($xml))
    return $output;
  
  $items = array();
  foreach ($xml->channel->item as $item){
    $item->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    
    $items[] = array(
      'date' => xpath_item($item, 'pubDate'),
      'title' => xpath_item($item, 'title'),
      'user' => xpath_item($item, 'dc:creator'),
      'tags' => xpath_items($item, 'category'),
    );
  }
  
  $output[1] = $items;
  
  return $output;
}

