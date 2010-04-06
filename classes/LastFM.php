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
      
    $this->get_data('http://ws.audioscrobbler.com/2.0/', array_merge($default, $params));
    
    if (isset($this->data->error))
      return FALSE;
  }
}
