<?php

class LastFM extends API {
  public $doc = 'http://www.last.fm/api/';
  public $def = 'LASTFM'; // http://www.last.fm/api/account
  
  function call($method, $params){
    $default = array(
      'api_key' => Config::get('LASTFM'),
      'method' => $method,
      'format' => 'json',
      );
      
    $json = $this->get_data('http://ws.audioscrobbler.com/2.0/', array_merge($default, $params));
    debug($json);
    
    if (!is_object($json) || isset($json->error))
      return FALSE;
      
    return $json;
  }
}
