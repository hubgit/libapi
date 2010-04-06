<?php

class Bitly extends API {
  public $doc = 'http://bit.ly/apidocs';
  public $def = array('BITLY_USER', 'BITLY');
  
  public $results;
  
  function metadata($uri, $data = array()){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];
            
    if (strpos($uri, 'http://bit.ly/') !== 0)
      $uri = $this->shorten($uri);
    
    if (!$uri)
      return FALSE;  

    $this->get_data('http://api.bit.ly/info', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'shortUrl' => $uri,
      ));
      
    $this->results = $this->data;
  }

  function shorten($url){
    $this->get_data('http://api.bit.ly/shorten', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'longUrl' => $url,
      ));
      
    if ($this->data->statusCode != 'OK')
      return FALSE;
  
    return $this->data->results->{$url}->shortUrl;
  }

  function stats($url){
    $this->get_data('http://api.bit.ly/stats', array(
      'login' => Config::get('BITLY_USER'),
      'apiKey' => Config::get('BITLY'),
      'version' => '2.0.1',
      'format' => 'json',
      'shortUrl' => $url,
      ));
      
    if ($this->data->statusCode != 'OK')
      return FALSE;

    return $this->data->results->{$url};
  }
}
