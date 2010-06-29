<?php

// 1. Register an app: http://www.mendeley.com/oapi/
// 2. In Config.php:
// 'MENDELEY_CONSUMER_KEY' => 'YOUR KEY',
// 'MENDELEY_CONSUMER_SECRET' => 'YOUR SECRET',

class Mendeley extends API {
  public $doc = 'http://www.mendeley.com/oapi/methods/';
  public $server = 'http://www.mendeley.com/oapi/';
  
  private $request_token_url = 'http://www.mendeley.com/oauth/request_token/';
  private $access_token_url = 'http://www.mendeley.com/oauth/access_token/';
  private $authorize_url = 'http://www.mendeley.com/oauth/authorize/';
  
  function require_oauth(){
    if (!(Config::$properties['MENDELEY_TOKEN'] && Config::$properties['MENDELEY_TOKEN_SECRET']))
      oauth_authorize('MENDELEY', array('request_token' => $this->request_token_url, 'authorize' => $this->authorize_url, 'access_token' => $this->access_token_url));
      
    $this->oauth = array(
      'consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'),
      'consumer_secret' => Config::get('MENDELEY_CONSUMER_SECRET'),
      'token' => Config::get('MENDELEY_TOKEN'),
      'secret' => Config::get('MENDELEY_TOKEN_SECRET'),
      );
  }

  function search($terms, $start = 0, $n = 20){
    $page = ($start * n) + 1;    
    $this->get_data($this->server . 'documents/search/' . rawurlencode($terms), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'), 'page' => $page, 'items' => $n));      
    $this->total = $this->data->total_results;
    $this->results = $this->data->documents;
  }
  
  function document_details($id, $type = NULL){
    $this->get_data($this->server . 'documents/details/' . rawurlencode($id), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'), 'type' => $type));
  }
  
  function document_related($id){
    $this->get_data($this->server . 'documents/related/' . rawurlencode($id), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY')));
    $this->total = $this->data->total_results;
    $this->results = $this->data->document_ids;
  }
}

