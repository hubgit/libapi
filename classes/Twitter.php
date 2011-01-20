<?php

// 1. Register an app: http://dev.twitter.com/apps
// 2. In Config.php:
// 'TWITTER_CONSUMER_KEY' => 'YOUR KEY',
// 'TWITTER_CONSUMER_SECRET' => 'YOUR SECRET',

class Twitter extends API {
  public $doc = 'http://apiwiki.twitter.com/Twitter-API-Documentation';
  //public $def = 'TWITTER_AUTH'; // http://apiwiki.twitter.com/Authentication - username:password for basic authentication
  public $server = 'http://api.twitter.com/1/';

  public $n = 200;
  public $max = 3200; // maximum 3200 items available through the API
  
  private $request_token_url = 'https://api.twitter.com/oauth/request_token';
  private $access_token_url = 'https://api.twitter.com/oauth/access_token';
  private $authorize_url = 'https://api.twitter.com/oauth/authorize';
  
  function oauth_init(){
    if (!(Config::$properties['TWITTER_TOKEN'] && Config::$properties['TWITTER_TOKEN_SECRET']))
      return oauth_authorize('TWITTER', array('request_token' => $this->request_token_url, 'authorize' => $this->authorize_url, 'access_token' => $this->access_token_url));
      
    $this->oauth = array(
      'consumer_key' => Config::get('TWITTER_CONSUMER_KEY'),
      'consumer_secret' => Config::get('TWITTER_CONSUMER_SECRET'),
      'token' => Config::get('TWITTER_TOKEN'),
      'secret' => Config::get('TWITTER_TOKEN_SECRET'),
      );
  }
  
  function authorize(){
    $oauth = new OAuth(Config::get('TWITTER_CONSUMER_KEY'), Config::get('TWITTER_CONSUMER_SECRET'), OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->enableDebug();
    
    try {
      $request_token = $oauth->getRequestToken($this->request_token_url);
    } catch (OAuthException $e){ debug($oauth->debugInfo); };
      
    $url = $this->authorize_url . '?' . http_build_query(array('oauth_token' => $request_token['oauth_token'], 'callback_url'));
    print 'Authorize: ' . $url  . "\n";  
    system(sprintf('open %s', escapeshellarg($url)));
    fwrite(STDOUT, "Enter the PIN: ");
    $verifier = trim(fgets(STDIN));

    //$oauth->setToken($token, $request_token['oauth_token_secret']);
    //$access_token = $oauth->getAccessToken($this->access_token_url);
    
    $oauth->setToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
    try {
      $access_token = $oauth->getAccessToken($this->access_token_url, NULL, $verifier);
    } catch (OAuthException $e){ debug($oauth->debugInfo); };
    
    
    printf("'TWITTER_TOKEN' => '%s',\n'TWITTER_TOKEN_SECRET' => '%s',\n", $access_token['oauth_token'], $access_token['oauth_token_secret']);
    exit();
  }

  function followers($user, $id = NULL, $cursor = -1){      
    $this->oauth_init();
    $this->get_data($this->server . 'followers/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));      
    $this->cursor = $this->data->next_cursor;
    $this->results = $this->data->ids;
  }

  function friends($user, $id = NULL, $cursor = -1){
    $this->oauth_init();
    $this->get_data($this->server . 'friends/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));      
    $this->cursor = $this->data->next_cursor;
    $this->results = $this->data->ids;
  }

  function user($user, $id = NULL){
    $this->oauth_init();
    $this->get_data($this->server . 'users/show.json', array('screen_name' => $user, 'user_id' => $id));
  }

  function content_by_user($user, $max = 0, $from = 1){
    $this->oauth_init();
    $http = array('header' => sprintf('Authorization: Basic %s', base64_encode(Config::get('TWITTER_AUTH'))));

    $from = $this->get_latest($from, 1); // 1 = earliest status id

    $n = $max ? min($this->n, $this->max, $max) : $this->n;
    $page = 1; // pages start at 1

    $count = 0;
    do {
     $this->get_data(
       $this->server . 'statuses/user_timeline.json',
       array(
        'screen_name' => $user,
        'since_id' => $from,
        'count' => $n,
        'page' => $page,
        ), 'json', $http
      );

      if (empty($this->data) || $this->data->error)
        break;

      foreach ($this->data as $item){
        if ($this->output_dir){
          $out = sprintf('%s/%s.js', $this->output_dir, preg_replace('/\D/', '', $item->id)); // can't use %d as $item->id is too big, so sanitise by removing non-numeric characters
          file_put_contents($out, json_encode($item));
        }
        else
          $this->results[] = $item;
      }

      if ($this->output_dir && $page == 1) // always in descending order
        file_put_contents($this->output_dir . '/latest', $this->data[0]->id);

      if (($page * $n) >= $max)
        break;

      sleep(1);
      $page++;
    } while (!empty($this->data));
  }
}

