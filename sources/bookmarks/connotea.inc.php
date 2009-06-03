<?php

# 

return defined('CONNOTEA_AUTH');

function bookmarks_connotea($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'http://dx.doi.org/' . $q['doi'];
    
  if (!$uri = $q['uri'])
    return FALSE;
    
  $auth = explode(':', CONNOTEA_AUTH);

  $xml = get_data(sprintf('http://%s:%s@www.connotea.org/data/uri/%s', urlencode($auth[0]), urlencode($auth[1]), md5($uri)), array(), 'xml');
  
  debug($xml);
    
  if (!is_object($xml))
    return FALSE;
    
  $items = array();
  foreach ($xml->Post as $item){ 
    $item->registerXPathNamespace('c', 'http://www.connotea.org/2005/01/schema#');  
    $item->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    
    $items[] = array(
      'date' => xpath_item($item, 'c:created'),
      'title' => xpath_item($item, 'c:title'),
      'user' => xpath_item($item, 'dc:subject'),
      'description' => xpath_item($item, 'c:description'),
      'tags' => xpath_items($item, 'dc:subject'),
    );   
  }
  
  return array(count($items), $items);
}

