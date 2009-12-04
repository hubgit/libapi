<?php

class Flickr extends API {
  public $doc = 'http://www.flickr.com/services/api/flickr.photos.getInfo.htm';
  public $def = array('FLICKR', 'FLICKR_SECRET');
  
  function __construct(){ 
    parent::__construct();
    
    $this->frobfile = '/tmp/flickr-frob';
           
    if (!defined('FLICKR_TOKEN'))
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
    
    $data =$this->get_data('http://www.flickr.com/services/rest/', $params, 'php');
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
      'api_key' => Config::get('FLICKR'),
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
    file_put_contents(dirname(__FILE__) . '/../config.inc.php', sprintf("\ndefine('%s', '%s');\n", $key, $value), FILE_APPEND) or die('Could not write to config.php');
  }

  function content_by_user($q){
    if (!$user = $q['user'])
      return FALSE;
    
    if (isset($q['output']))
      $output_dir = output_dir($q['output']);
    
    if (isset($q['from']))
      $from = $q['from'];
    else if ($output_dir && file_exists($output_dir . '/latest'))
      $from = file_get_contents($output_dir . '/latest');
    else
      $from = 0; // 1970-01-01T00:00:00Z
   
    $n = 500;
    $page = 1; // pages start at 1
  
    $items = array();
   
    do {
      $params = array(
        'user_id' => $user,
        'min_upload_date' => $from,
        'per_page' => $n,
        'page' => $page,
        'sort' => 'date-posted-asc',
        );

     $data = $this->api('flickr.photos.search', $params);
  
     //debug($data);
    
      if (!is_array($data) || $data['stat'] != 'ok' || empty($data['photos']['photo']))
        return FALSE;
    
      foreach ($data['photos']['photo'] as $photo){
        if ($output_dir){
          $id = preg_replace('/\D/', '', $photo['id']); // can't use %d as too big, so sanitise by removing non-numeric characters
        
          $out = sprintf('%s/%s.js', $output_dir, $id);
          if (file_exists($out))
            continue;
          
          $result = $api->run($photo);
          $item = $result['flickr'];
        
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
        
          file_put_contents(sprintf('%s/%s%s.jpg', $output_dir, $id, $suffix), file_get_contents($image_url));
          file_put_contents($out, json_encode($item)); // write this once image has been retrieved, as a marker of success
        
          file_put_contents($output_dir . '/latest', $item['dateuploaded']);
        }
        else
          $items[] = $photo;
      }
  
      sleep(1);
    
    } while ($page++ < $data['photos']['pages']);

    return $items;
  }
  
  // http://www.flickr.com/services/api/flickr.photos.getInfo.htm
  function metadata($q){    
    if (!$id = $q['id'])
     return FALSE;

    $data = $this->get_data('http://api.flickr.com/services/rest/', array(
      'api_key' => Config::get('FLICKR'),
      'format' => 'php_serial',
      'method' => 'flickr.photos.getInfo',
      'photo_id' => $id,
      'secret' => $q['secret'],
      ), 'php');

    //debug($data);

    if (!is_array($data) || $data['stat'] != 'ok')
      return FALSE;

    return $data['photo'];
  }
  
  // http://www.flickr.com/services/api/flickr.photos.search.html
  function search($params = array()){ 
    if (is_string($params))
      $params = array('text' => $params);  

    $data = $this->get_data('http://api.flickr.com/services/rest/', array_merge(array(
      'api_key' => Config::get('FLICKR'),
      'format' => 'php_serial',
      'method' => 'flickr.photos.search',
      'per_page' => 20,
      ), $params), 'php');

    //debug($data);

    if (!is_array($data))
      return FALSE;
      
    $meta = array(
      'total' => (int) $data['photos']['total'],
      'pages' => (int) $data['photos']['pages'],
      );

    return array($data['photos']['photo'], $meta);
  }
}