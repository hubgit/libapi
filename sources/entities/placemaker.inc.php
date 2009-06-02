<?php

# http://code.google.com/apis/maps/documentation/geocoding/index.html

return defined('YAHOO_KEY');

function entities_placemaker($text){
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
  foreach ($xml->xpath("y:document/y:placeDetails/y:place") as $item){
    $id = (int) $item->woeId;
    $type = (string) $item->type;
    $entities[$type][$id] = array(
      'title' => (string) $item->name,
      'lat' => (float) $item->centroid->latitude,
      'lng' => (float) $item->centroid->longitude,
      'score' => (int) $item->confidence,
      );
  }
  
  $references = array();
  foreach ($xml->xpath("y:document/y:referenceList/y:reference") as $item){
    $id = (string) $item->woeIds;
    $references[] = array('start' => (int) $item->start, 'end' => (int) $item->end, 'text' => (string) $item->text, 'entity' => $id);
  }
      
  return array($entities, $references);
}
