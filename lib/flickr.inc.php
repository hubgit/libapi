<?php

class Flickr {
  function __construct(){ 
    $this->frobfile = '/tmp/flickr-frob';
           
    if (!defined('FLICKR_TOKEN'))
    	$this->get_token();
  }
  
  // Generic API method
  function api($method, $params = array()){ 
    $default = array(
      'method' => $method,
	    'api_key'	=> FLICKR_KEY,
	    'format'	=> 'php_serial',
    );
    
    if ($this->token)
      $params['auth_token'] = $this->token;
    
    $params = array_merge($default, $params);
    $params['api_sig'] = $this->sign($params); 
    
    $data = get_data('http://www.flickr.com/services/rest/', $params, 'php');
    debug($data);
    
    switch($data['stat']){
      case 'ok':
        return $data;
      break;
      
      case 'fail':
        debug($method . ' failed: ' . $data['message'] . "\n");
      break;
      
      default:
        debug($data);
        exit();
      break;
    }   
  }
  
  // Generate api_sig from array of parameters
  function sign($params){
    ksort($params);
    return md5(FLICKR_SECRET . implode('', array_map(array($this, 'flatten'), array_keys($params), array_values($params))));
  }
  
    // Concatenate key/value pairs into a flat string
  function flatten($key, $value){
    return $key . $value;
  }
   
  // Fetch a user-specific token
  function get_token(){
    if (!file_exists($this->frobfile) || time() - filemtime($this->frobfile) > 3600)
    	$this->get_frob();
    	
    $result = $this->api('flickr.auth.getToken', array('frob' => file_get_contents($this->frobfile)));
    
    unlink($this->frobfile); // frob is only valid for one request
    
    if (!$result['auth']['token']['_content'] || $result['auth']['perms']['_content'] != 'read')
      exit('Unable to get authentication token');
    
    $this->write_config('FLICKR_TOKEN', $result['auth']['token']['_content']);
  }
  
    // fetch a temporary frob for authentication
  function get_frob(){
    unlink($this->frobfile);
    $result = $this->api('flickr.auth.getFrob');
    if (!$result['frob']['_content'])
      exit('No frob returned');
      
    $frob = $result['frob']['_content'];
    file_put_contents($this->frobfile, $frob);
    $this->show_authentication_url($frob);
   }
   
   function show_authentication_url($frob){ 
    $params = array(
      'api_key' => FLICKR_KEY,
      'frob' => $frob,
      'perms' => 'read',
      );
    
    $params['api_sig'] = $this->sign($params);
    
    $url = 'http://www.flickr.com/services/auth/' . '?' . http_build_query($params);
    debug('Sign in to Flickr in your browser and allow this application to access your photos:' . "\n$url");
    //exec(sprintf("gnome-open %s" , escapeshellarg($url)));
    exit();
  }
  
  function write_config($key, $value){
    define($key, $value);
    file_put_contents(__DIR__ . '/../config.inc.php', sprintf("\ndefine('%s', '%s');\n", $key, $value), FILE_APPEND) or die('Could not write to config.php');
  }
}
