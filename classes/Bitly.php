<?php

class Bitly extends API {
  public $doc = 'http://bit.ly/apidocs';
  public $def = array('BITLY_USER', 'BITLY');
  
  function metadata($q){
    if (!$q['uri'] && $q['doi'])
      $q['uri'] = 'http://dx.doi.org/' . $q['doi'];
        
    if (!$uri = $q['uri'])
      return FALSE;
    
    if (strpos($uri, 'http://bit.ly/') !== 0)
      $uri = $this->shorten($uri);
    
    if (!$uri)
      return FALSE;  

    $json = $this->get_data('http://api.bit.ly/info', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'shortUrl' => $uri,
      ));
  
    //debug($json);
    
    if (!is_object($json))
      return FALSE;
  
    $data = array('raw' => $json);
  
    //debug($this->stats($uri));
    return $data;
  }

  function shorten($url){
    $json = $this->get_data('http://api.bit.ly/shorten', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'longUrl' => $url,
      ));
  
    //debug($json);
    
    if (!is_object($json) || $json->statusCode != 'OK')
      return FALSE;
  
    return $json->results->{$url}->shortUrl;
  }

  function stats($url){
    $json = $this->get_data('http://api.bit.ly/stats', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'shortUrl' => $url,
      ));
  
    //debug($json);
    
    if (!is_object($json) || $json->statusCode != 'OK')
      return FALSE;
  
    return $json->results->{$url}->shortUrl;
  }
}
