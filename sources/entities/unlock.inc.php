<?php

# http://unlock.edina.ac.uk:81/service-results/

//return defined('UNLOCK_KEY');

function entities_unlock($q){
  if (!$text = $q['text'])
    return FALSE;
    
  $boundary = '---------------------' . substr(md5(time()), 0, 10);
    
  $params = array(
    'type' => 'plain',
    'gazetteer' => 'geonames',
    'outputFormat' => 'basic',
    'apiKey' => '12345',
    'document' => $text,
  );
  
  //$b64_text = chunk_split(base64_encode($text));
  
  /*
  $data = "--$boundary\n";
  foreach ($params as $key => $value)
    $data .= "Content-Disposition: form-data; name='$key'\n\n$value\n--$boundary\n";

  $data .= "Content-Disposition: form-data; name='document'; filename='news.txt'\nContent-Type: text/plain\n\n$text\n--$boundary--\n\n";

  $data = str_replace("\n", "\r\n", $data);
  
  $http = array('method' => 'POST', 'content' => $data, 'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary);
  */
  
  $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
  
  debug($params);
  
  $xml = get_data('http://unlock.edina.ac.uk:81/service-results/', array(), 'xml', $http);
  
  //debug($xml);
  
  exit();
  
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
