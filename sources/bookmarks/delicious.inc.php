<?php

# 

function bookmarks_delicious($q){
  if (!$uri = $q['uri'])
    return FALSE;
    
  $json = get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));
  
  debug($json);
    
  if (!is_object($json) || empty($json))
    return FALSE;
    
  $result = $json[0];
    
  return array($result->total_posts, $items, array('tags' => (array) $result->top_tags));
}

