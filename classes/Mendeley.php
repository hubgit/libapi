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
  
  function oauth_init(){
    if (!(Config::get('MENDELEY_TOKEN', false) && Config::get('MENDELEY_TOKEN_SECRET', false)))
      oauth_authorize('MENDELEY', array('request_token' => $this->request_token_url, 'authorize' => $this->authorize_url, 'access_token' => $this->access_token_url));
      
    $this->oauth = array(
      'consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'),
      'consumer_secret' => Config::get('MENDELEY_CONSUMER_SECRET'),
      'token' => Config::get('MENDELEY_TOKEN'),
      'secret' => Config::get('MENDELEY_TOKEN_SECRET'),
      );
  }

  function call($path, $params = array()){
    $defaults = array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'));
    return $this->get_data($this->server . $path, array_merge($defaults, $params), 'json');
  }

  function search($terms, $start = 0, $n = 20){
    $page = ($start * n) + 1;    
    $this->call('documents/search/' . rawurlencode($terms), array('page' => $page, 'items' => $n));      
    $this->total = $this->data->total_results;
    return $this->results = $this->data->documents;
  }
  
  function document_details($id, $type = NULL){
    if ($type == 'doi') $id = str_replace('/', '%2F', $id); // workaround
    return $this->call('documents/details/' . rawurlencode($id), array('type' => $type));
  }
  
  function document_related($id, $start = 0, $n = 20){
    $this->call('documents/related/' . rawurlencode($id), array('page' => $page, 'items' => $n));
    $this->total = $this->data->total_results;
    return $this->data->document_ids;
  }

  function document_authored($name, $start = 0, $n = 20){
    $this->call('documents/authored/' . rawurlencode($name), array('page' => $page, 'items' => $n));
    $this->total = $this->data->total_results;
    return $this->data->document_ids;
  }

  function document_tagged($tag, $start = 0, $n = 20){
    $this->call('documents/tagged/' . rawurlencode($tag), array('page' => $page, 'items' => $n));
    $this->total = $this->data->total_results;
    return $this->data->document_ids;
  }

  function create_group($name, $type = 'open'){
    $this->oauth_init();
    $data = array('group' => array('name' => $name, 'type' => $type));
    $http = array('method' => 'POST', 'content' => json_encode($data));
    $this->call('library/groups/', array(), 'json', $http);
  }
}

