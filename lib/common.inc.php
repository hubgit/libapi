<?php

function get_data($url, $params = array(), $format = 'json', $http = array()){
  debug($params);
  if (!empty($params))
    $url .= '?' . http_build_query($params);
  
  //$http['header'] .= (empty($http['header']) ? '' : "\n") . 'Accept: ' . accept_header($format);
  $context = empty($http) ? NULL : stream_context_create(array('http' => $http));
  
  $data = file_get_contents($url, NULL, $context);
  //debug($data);
  //debug($http_response_header);
  
  return format_data($format, $data);
}

function get_data_curl($url, $params = array(), $format = 'json', $http = array(), $curl_params = array()){
  debug($params);
  if (!empty($params))
    $url .= '?' . http_build_query($params);
        
  $curl = curl_init($url);
  
  // array_merge doesn't preserve numeric keys
  curl_setopt_array($curl, $curl_params + array(
    CURLOPT_CONNECTTIMEOUT => 60, // 1 minute
    CURLOPT_TIMEOUT => 60*60*24, // 1 day
    CURLOPT_RETURNTRANSFER => 1, // return contents
  ));
  
  if (isset($http['header']))
    curl_setopt($curl, CURLOPT_HTTPHEADER, array($http['header']));

  $data = curl_exec($curl);  
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  debug('Status: ' . $status);  
  //debug($data);

  curl_close($curl);
  return format_data($format, $data); 
}

function format_data($format, $data){
  switch ($format){
    case 'json':
      return json_decode($data);
    case 'xml':
      return simplexml_load_string($data, NULL, LIBXML_NOCDATA);
    case 'html':
      return simplexml_import_dom(@DOMDocument::loadHTML($data));
    case 'rdf':
      return simplexml_load_string($data, NULL, LIBXML_NOCDATA); // TODO: parse RDF
    case 'php':
      return unserialize($data);
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

# http://developer.yahoo.com/yql/
function yql($query, $args = array(), $format = 'json'){
  if (!empty($args))
    $query = vsprintf($query, is_array($args) ? $args : array($args));
  
  return get_data('http://query.yahooapis.com/v1/public/yql', array(
    'q' => $query,
    'format' => $format,
    ));
}

function base64_encode_file($t){
  return strtr(base64_encode($t), '+/', '-_') ;
}

function base64_decode_file($t){
  return base64_decode(strtr($t, '-_', '+/'));
}

function snippet($text, $start, $end, $pad = 50){
  $length = mb_strlen($text);
  $position = array($start, $end);
  
  $start -= $pad;
  $start = max($start, 0);
  
  while ($start > 0 && preg_match('/\S/', mb_substr($text, $start, 1)))
    $start--;
    
  $end += $pad;
  $end = min($end, $length);
  while ($end < $length && preg_match('/\S/', mb_substr($text, $end, 1)))
    $end++;
    
  return mb_substr($text, $start, $position[0] - $start) . '{{{' . mb_substr($text, $position[0], $position[1] - $position[0]) . '}}}' . mb_substr($text, $position[1], $end - $position[1]);
}

function output_folder($dir){
  $dir = DATA_DIR . $dir;
  
  if (!file_exists($dir))
    mkdir($dir, 0755, TRUE);
  if (!is_dir($dir))
    exit('Could not create output folder ' . $dir);
  return $dir;
}

function space_prefix_html_elements($html){
  return preg_replace("/<(p|div|br|h1|h2|h3|h4|h5|h6|ol|ul|li|pre|address|blockquote|dl|div|fieldset|form|hr|noscript|table|td|dd|dt)(\s|>)/", ' <$1$2', $html);
}