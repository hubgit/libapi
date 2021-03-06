<?php

class Flickr extends API {
  public $doc = 'http://www.flickr.com/services/api/flickr.photos.getInfo.htm';
  public $def = array('FLICKR', 'FLICKR_SECRET');
  
  private $frobfile;
  private $tokenfile;
  private $frob;
  private $token;
  
  function __construct(){ 
    parent::__construct();
   
    $this->auth_dir = $this->get_output_dir('flickr/auth');
    
    $this->frobfile = $this->auth_dir . '/frob.txt';
    $this->tokenfile = $this->auth_dir . '/token.txt'; // warning: stored in libapi data dir - should store locally, or set read permissions?
    
    if (file_exists($this->tokenfile))
      $this->token = file_get_contents($this->tokenfile);
    	        
    if (!$this->token)
    	$this->get_token();
  }
  
  // Generic API method
  function api($method, $params = array()){ 
    $default = array(
      'method' => $method,
	    'api_key'	=> Config::get('FLICKR'),
	    'format'	=> 'php_serial',
    );
    
    if ($this->token)
      $params['auth_token'] = $this->token;
    
    $params = array_merge($default, $params);
    $params['api_sig'] = $this->sign($params); 
    
    $this->get_data('http://www.flickr.com/services/rest/', $params, 'php');
    
    switch($this->data['stat']){
      case 'ok':
      break;
      
      case 'fail':
        debug($method . ' failed: ' . $this->data['message'] . "\n");
        return FALSE;
      break;
      
      default:
        debug($this->data);
        exit();
      break;
    }   
  }
  
  // Generate api_sig from array of parameters
  function sign($params){
    ksort($params);
    return md5(Config::get('FLICKR_SECRET') . implode('', array_map(array($this, 'flatten'), array_keys($params), array_values($params))));
  }
  
    // Concatenate key/value pairs into a flat string
  function flatten($key, $value){
    return $key . $value;
  }
   
  // Fetch a user-specific token
  function get_token(){
    if (!file_exists($this->frobfile) || time() - filemtime($this->frobfile) > 3600)
    	$this->get_frob();
    	
    $this->api('flickr.auth.getToken', array('frob' => file_get_contents($this->frobfile)));
    
    unlink($this->frobfile); // frob is only valid for one request
    
    if (!$this->data['auth']['token']['_content'] || $this->data['auth']['perms']['_content'] != 'read')
      exit('Unable to get authentication token');
    
    $this->token = $this->data['auth']['token']['_content'];
    
    file_put_contents($this->tokenfile, $this->token) or die('Could not write to ' . $this->tokenfile);
  }
  
  // fetch a temporary frob for authentication
  function get_frob(){
    if (file_exists($this->frobfile))
      unlink($this->frobfile);
    
    $this->api('flickr.auth.getFrob');
    if (!$this->data['frob']['_content'])
      exit('No frob returned');
      
    $frob = $this->data['frob']['_content'];
    file_put_contents($this->frobfile, $frob);
    
    $params = array(
      'api_key' => Config::get('FLICKR'),
      'frob' => $frob,
      'perms' => 'read',
      );
    
    $params['api_sig'] = $this->sign($params);
    
    $url = 'http://www.flickr.com/services/auth/' . '?' . http_build_query($params);
    debug("Sign in to Flickr in your browser and allow this application to access your photos:\n$url");
    //exec(sprintf("gnome-open %s" , escapeshellarg($url)));
    exit();
  }

  // http://idgettr.com/
  function content_by_user($user, $from = 0){    
    $from = $this->get_latest($from); // 0 = 1970-01-01T00:00:00Z
   
    $n = 500;
    $page = 1; // pages start at 1
     
    do {
      $params = array(
        'user_id' => $user,
        'min_upload_date' => $from,
        'per_page' => $n,
        'page' => $page,
        'sort' => 'date-posted-asc',
        );

     $this->api('flickr.photos.search', $params);
      
      if ($this->data['stat'] != 'ok' || empty($this->data['photos']['photo']))
        return FALSE;
    
      foreach ($this->data['photos']['photo'] as $photo){
        if ($this->output_dir){
          $id = preg_replace('/\D/', '', $photo['id']); // can't use %d as too big, so sanitise by removing non-numeric characters
        
          $out = sprintf('%s/%s.js', $this->output_dir, $id);
          if (file_exists($out))
            continue;
          
          $item = $this->metadata($photo);
          //$item = $result['flickr'];
        
          if ($item['originalformat']){
            $format = $item['originalformat'];
            $secret = $item['originalsecret'];      
            $suffix = '_o';
          }
          else{
            $format = 'jpg';
            $secret = $item['secret'];      
            $suffix = '';
          }
        
          $image_url = sprintf('http://farm%d.static.flickr.com/%d/%s_%s%s.%s', $item['farm'], $item['server'], $item['id'], $secret, $suffix, $format);
          debug($image_url);
        
          file_put_contents(sprintf('%s/%s%s.jpg', $this->output_dir, $id, $suffix), file_get_contents($image_url));
          file_put_contents($out, json_encode($item)); // write this once image has been retrieved, as a marker of success
        
          file_put_contents($this->output_dir . '/latest', $item['dateuploaded']);
        }
        else
          $this->results[] = $photo;
      }
  
      sleep(1);
    
    } while ($page++ < $this->data['photos']['pages']);
  }
  
  // http://www.flickr.com/services/api/flickr.photos.getInfo.htm
  function metadata($id, $secret = NULL){        
    $this->get_data('http://api.flickr.com/services/rest/', array(
      'api_key' => Config::get('FLICKR'),
      'format' => 'php_serial',
      'method' => 'flickr.photos.getInfo',
      'photo_id' => $id,
      'secret' => $secret,
      ), 'php');


    if ($this->data['stat'] != 'ok')
      return FALSE;

    return $this->data['photo'];
  }
  
  // http://www.flickr.com/services/api/flickr.photos.search.html
  function search($params = array()){ 
    if (is_string($params))
      $params = array('text' => $params);  

    $this->get_data('http://api.flickr.com/services/rest/', array_merge(array(
      'api_key' => Config::get('FLICKR'),
      'format' => 'php_serial',
      'method' => 'flickr.photos.search',
      'per_page' => 20,
      ), $params), 'php');

    $this->total = (int) $this->data['photos']['total'];
    $this->pages = (int) $this->data['photos']['pages'];
    $this->results = $this->data['photos']['photo'];
  }
}