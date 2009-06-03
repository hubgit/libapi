<?php

function get_data($url, $params = array(), $format = 'json', $http = array()){
  debug($params);
  
  if (!empty($params))
    $url .= '?' . http_build_query($params);
    
  $context = empty($http) ? NULL : stream_context_create(array('http' => $http));
    
  $data = file_get_contents($url, NULL, $context);
  debug($data);
  
  switch ($format){
    case 'json':
      return json_decode($data);
    case 'xml':
      return simplexml_load_string($data, NULL, LIBXML_NOCDATA);
    case 'raw':
      return $data;
  }
}

function debug($arg){
  print_r($arg);
  print "\n";
}

function xpath_item($xml, $query){
  $nodes = $xml->xpath($query);
  if (!empty($nodes))
    return (string) $nodes[0];
  return FALSE;
}

function xpath_items($xml, $query){
  $nodes = $xml->xpath($query);
  $items = array();
  if (!empty($nodes))
    foreach ($nodes as $node)
      $items[] = (string) $node;
  return $items; 
}