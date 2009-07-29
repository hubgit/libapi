<?php

# http://developer.yahoo.com/geo/placemaker/

return defined('YAHOO_KEY');

function entities_placemaker($q){
  if (!$text = $q['text'])
    return FALSE;
    
  $params = array(
    'appid' => YAHOO_KEY,
    'documentType' => 'text/plain',
    'documentContent' => $text,
  );
  
  $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
  
  $xml = get_data('http://wherein.yahooapis.com/v1/document', array(), 'xml', $http);
  
  //debug($xml);
  
  if (!is_object($xml))
    return array();
  
  $xml->registerXPathNamespace('y', 'http://wherein.yahooapis.com/v1/schema');
  
  $entities = array();
  $nodes = $xml->xpath("y:document/y:placeDetails/y:place");
  if (!empty($nodes)){
    foreach ($nodes as $item){
      $id = (int) $item->woeId;
      $type = (string) $item->type;
      $entities[$type][$id] = array(
        'title' => (string) $item->name,
        'lat' => (float) $item->centroid->latitude,
        'lng' => (float) $item->centroid->longitude,
        'score' => (int) $item->confidence,
        );
    }
  }
  
  $references = array();
  $nodes = $xml->xpath("y:document/y:referenceList/y:reference");
  if (!empty($nodes)){
    foreach ($nodes as $item){
      $id = (string) $item->woeIds;
      $references[] = array(
        'start' => (int) $item->start, 
        'end' => (int) $item->end, 
        'text' => (string) $item->text, 
        'entity' => $id,
        'snippet' => snippet($text, (int) $item->start, (int) $item->end),
        );
    }
  }
      
  return array($entities, $references);
}
