<?php

function get_data($url, $params, $format = 'json'){
  debug($params);
  $data = file_get_contents($url . '?' . http_build_query($params));
  
  switch ($format){
    case 'json':
      return json_decode($data);
    case 'xml':
      return simplexml_load_string($data);
  }
}

function debug($arg){
  print_r($arg);
  print "\n";
}
