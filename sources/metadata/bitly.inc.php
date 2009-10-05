<?php

# http://bit.ly/apidocs

return (defined('BITLY_USER') && defined('BITLY_KEY'));

function metadata_bitly($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'http://dx.doi.org/' . $q['doi'];
        
  if (!$uri = $q['uri'])
    return FALSE;
    
  if (strpos($uri, 'http://bit.ly/') !== 0)
    $uri = bitly_shorten($uri);
    
  if (!$uri)
    return FALSE;  

  $json = get_data('http://api.bit.ly/info', array(
    'login' => BITLY_USER,
    'apiKey' => BITLY_KEY,
    'version' => '2.0.1',
    'format' => 'json',
    'shortUrl' => $uri,
    ));
  
  debug($json);
    
  if (!is_object($json))
    return FALSE;
  
  $data = array('raw' => $json);
  
  //debug(bitly_stats($uri));
  return $data;
}

function bitly_shorten($url){
  $json = get_data('http://api.bit.ly/shorten', array(
    'login' => BITLY_USER,
    'apiKey' => BITLY_KEY,
    'version' => '2.0.1',
    'format' => 'json',
    'longUrl' => $url,
    ));
  
  debug($json);
    
  if (!is_object($json) || $json->statusCode != 'OK')
    return FALSE;
  
  return $json->results->{$url}->shortUrl;
}

function bitly_stats($url){
  $json = get_data('http://api.bit.ly/stats', array(
    'login' => BITLY_USER,
    'apiKey' => BITLY_KEY,
    'version' => '2.0.1',
    'format' => 'json',
    'shortUrl' => $url,
    ));
  
  debug($json);
    
  if (!is_object($json) || $json->statusCode != 'OK')
    return FALSE;
  
  return $json->results->{$url}->shortUrl;
}
