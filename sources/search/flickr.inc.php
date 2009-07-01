<?php

# http://www.flickr.com/services/api/flickr.photos.search.html

return defined('FLICKR_KEY');

function search_flickr($params = array()){ 
  if (is_string($params))
    $params = array('text' => $params);  

  $data = get_data('http://api.flickr.com/services/rest/', array_merge(array(
    'api_key' => FLICKR_KEY,
    'format' => 'php_serial',
    'method' => 'flickr.photos.search',
    'per_page' => 20,
    ), $params), 'php');
  
  //debug($data);
  
  if (!is_array($data))
    return FALSE;
    
  return array((int) $data['photos']['total'], $data['photos']['photo'], (int) $data['photos']['pages']);
}
