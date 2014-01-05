<?php

class Yelp extends API {
  public $server = 'http://api.yelp.com/v2/';
  
  private $request_token_url = 'https://api.yelp.com/oauth/request_token';
  private $access_token_url = 'https://api.yelp.com/oauth/access_token';
  private $authorize_url = 'https://api.yelp.com/oauth/authorize';

   function oauth_init(){
     if (!(Config::$properties['YELP_TOKEN'] && Config::$properties['YELP_TOKEN_SECRET']))
       return oauth_authorize('YELP', array('request_token' => $this->request_token_url, 'authorize' => $this->authorize_url, 'access_token' => $this->access_token_url));

     $this->oauth = array(
       'consumer_key' => Config::get('YELP_CONSUMER_KEY'),
       'consumer_secret' => Config::get('YELP_CONSUMER_SECRET'),
       'token' => Config::get('YELP_TOKEN'),
       'secret' => Config::get('YELP_TOKEN_SECRET'),
       );
   }
  
  
  function search($term, $params = array()){
    $this->oauth_init();
    $defaults = array('term' => $term);
    $this->cache = false;
    $this->get_data($this->server . 'search', array_merge($defaults, $params), 'raw');
    $this->total = $this->data->total;
    return $this->items = $this->data->businesses;
  } 
}