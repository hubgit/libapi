<?php

function get_data($url, $params = array(), $format = 'json', $http = array()){
  debug($params);
  
  if (!empty($params))
    $url .= '?' . http_build_query($params);
  
  //$http['header'] .= (empty($http['header']) ? '' : "\n") . 'Accept: ' . accept_header($format);
  
  $context = empty($http) ? NULL : stream_context_create(array('http' => $http));
  
  $data = file_get_contents($url, NULL, $context);
  debug($data);
  debug($http_response_header);
  
  switch ($format){
    case 'json':
      return json_decode($data);
    case 'xml':
      return simplexml_load_string($data, NULL, LIBXML_NOCDATA);
    case 'rdf':
      return simplexml_load_string($data, NULL, LIBXML_NOCDATA); // TODO: parse RDF
    case 'raw':
    default:
      return $data;
  }
}

function accept_header($format){
  switch ($format){
    case 'json':
      return 'application/json, */*;q=0.2';
    case 'xml':
     return 'application/xml, */*;q=0.2';
    case 'rdf':
      return 'application/rdf+xml, */*;q=0.2';
    case 'raw':
    default:
      return '*/*';
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