<?php

# http://developer.nytimes.com/docs/article_search_api

return defined('NYTIMES_KEY');

function search_nytimes($q, $params = array()){
  if (!$q)
    return FALSE;

  $default = array(
    'query' => $q,
    'fields' => 'byline,body,date,title,url,des_facet',
    'api-key' => NYTIMES_KEY,
  );
  
  $json = get_data('http://api.nytimes.com/svc/search/v1/article', array_merge($default, $params));
  
  if (!is_object($json))
    return FALSE;
    
  return array($json->total, $json->results);
}

