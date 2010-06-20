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
  
  function __construct(){
    if (!(Config::$properties['MENDELEY_TOKEN'] && Config::$properties['MENDELEY_TOKEN_SECRET']))
      $this->authorize();
      
    $this->oauth = array(
      'consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'),
      'consumer_secret' => Config::get('MENDELEY_CONSUMER_SECRET'),
      'token' => Config::get('MENDELEY_TOKEN'),
      'secret' => Config::get('MENDELEY_TOKEN_SECRET'),
      );
  }
  
  function authorize(){
    $oauth = new OAuth(Config::get('MENDELEY_CONSUMER_KEY'), Config::get('MENDELEY_CONSUMER_SECRET'), OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->enableDebug();
    
    try {
      $request_token = $oauth->getRequestToken($this->request_token_url);
    } catch (OAuthException $e){ debug($oauth->debugInfo); };
      
    $url = $this->authorize_url . '?' . http_build_query(array('oauth_token' => $request_token['oauth_token'], 'callback_url'));
    print 'Authorize: ' . $url  . "\n";  
    system(sprintf('open %s', escapeshellarg($url)));
    fwrite(STDOUT, "Enter the PIN: ");
    $verifier = trim(fgets(STDIN));

    $oauth->setToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
    try {
      $access_token = $oauth->getAccessToken($this->access_token_url, NULL, $verifier);
    } catch (OAuthException $e){ debug($oauth->debugInfo); };
    
    printf("'MENDELEY_TOKEN' => '%s',\n'MENDELEY_TOKEN_SECRET' => '%s',\n", $access_token['oauth_token'], $access_token['oauth_token_secret']);
    exit();
  }

  function search($terms, $start = 0, $n = 20){
    $this->oauth = NULL;
    $page = ($start * n) + 1;    
    //$this->get_data($this->server . 'documents/search/' . rawurlencode($terms), array('page' => $page, 'items' => $n));      
    $this->get_data($this->server . 'documents/search/' . rawurlencode($terms), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY')));      
    $this->total = $this->data->total_results;
    $this->results = $this->data->documents;
  }
  
  function document_details($id, $type = NULL){
    $this->oauth = NULL;
    $this->get_data($this->server . 'documents/details/' . rawurlencode($id), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY'), 'type' => $type));
  }
  
  function document_related($id){
    $this->oauth = NULL;
    $this->get_data($this->server . 'documents/related/' . rawurlencode($id), array('consumer_key' => Config::get('MENDELEY_CONSUMER_KEY')));
    $this->total = $this->data->total_results;
    $this->results = $this->data->document_ids;
  }
}

