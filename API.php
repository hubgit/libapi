<?php

class API {
  public $input_dir;
  public $output_dir;
  
  function __construct(){
    if (isset($this->def) && !empty($this->def))
      if (is_array($this->def))
        foreach ($this->def as $def)
          $this->check_def($def);
      else
        $this->check_def($this->def);
  }
  
  static function __autoload($class){
    $file = sprintf('%s/classes/%s', LIBAPI_ROOT, $class);
    if (file_exists($file . '.private.php'))
      return require_once($file . '.private.php');
    else if (file_exists($file . '.php'))
      return require_once($file . '.php');
    
    $file = sprintf('%s/lib/%s.php', LIBAPI_ROOT, $class);
    if (file_exists($file))
      return require_once($file);
  }
  
  function check_def($def){
    if (Config::get($def) === FALSE)
      throw new Exception('Requirement not defined: ' . $def);
  }
  
  function get_data($url, $params = array(), $format = 'json', $http = array()){
    debug($params);
    if (!empty($params)){
      ksort($params);
      $url .= '?' . http_build_query($params);
    }

    debug($url);

    //$http['header'] .= (empty($http['header']) ? '' : "\n") . 'Accept: ' . accept_header($format);
    
    if (isset($http['file']))
      $http['content'] = file_get_contents($http['file']);
      
    /*
    if (!isset($http['proxy'])){
      $http['proxy'] = 'tcp://proxy.local:80';
      $http['request_fulluri'] = TRUE;
    }
    */
      
    $context = empty($http) ? NULL : stream_context_create(array('http' => $http));

    $data = file_get_contents($url, NULL, $context);
    //debug($data);
    debug($http_response_header);
    
    $this->http_response_header = $http_response_header;
    $this->parse_http_response_header();

    $h = explode(' ', $http_response_header[0], 3);
    $this->http_status = $h[1];
    debug('Status: ' . $this->http_status);

    return $this->format_data($format, $data);
  }

  function get_data_curl($url, $params = array(), $format = 'json', $http = array(), $curl_params = array()){  
    debug($params);
    if (!empty($params))
      $url .= '?' . http_build_query($params);

    $curl = curl_init($url);
    debug($url);

    // array_merge doesn't preserve numeric keys
    curl_setopt_array($curl, $curl_params + array(
      CURLOPT_CONNECTTIMEOUT => 60, // 1 minute
      CURLOPT_TIMEOUT => 60*60*24, // 1 day
      CURLOPT_RETURNTRANSFER => TRUE, // return contents
      //CURLOPT_FAILONERROR => TRUE,
      //CURLOPT_SSL_VERIFYPEER => FALSE, // FIXME: temporary fix for curl without SSL certificates
    ));

    if (isset($http['header']))
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($http['header']));
      
    if (isset($http['method'])){
      switch($http['method']){
        case 'POST':
          curl_setopt($curl, CURLOPT_POST, TRUE);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $http['content']);
        break;
        
        case 'PUT':
          curl_setopt($curl, CURLOPT_PUT, TRUE);
          if (is_string($http['file']))
            $http['file'] = fopen($http['file'], 'r');
            
          if (!isset($http['file']) && isset($http['content'])){
            $http['file'] = tmpfile();
            fwrite($http['file'], $http['content']);
            fseek($http['file'], 0);
          }
            
          $fstat = fstat($http['file']);
          curl_setopt($curl, CURLOPT_INFILE, $http['file']);
          curl_setopt($curl, CURLOPT_INFILESIZE, $fstat['size']);        
        break;
        
        case 'DELETE':
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        break;
        
        case 'GET':
        default:
        break;
      }
    }

    $data = curl_exec($curl);  
    $this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $this->http_info = array(curl_getinfo($curl));

    debug('Status: ' . $this->http_status);  
    debug($data);
    
    //file_put_contents(sys_get_temp_dir() . '/curl.xml', $data);
    
    curl_close($curl);
    if (isset($http['file']))
      fclose($http['file']);
    return $this->format_data($format, $data); 
  }

  function format_data($format, $data){
    switch ($format){
      case 'json':
        return json_decode($data);
      case 'xml':
        return simplexml_load_string($data, NULL, LIBXML_NOCDATA);
      case 'dom':
        return DOMDocument::loadXML($data);
      case 'html':
        return simplexml_import_dom(@DOMDocument::loadHTML($data));
      case 'html-dom':
        return @DOMDocument::loadHTML($data);
      case 'rdf':
        return simplexml_load_string($data, NULL, LIBXML_NOCDATA); // TODO: parse RDF
      case 'php':
        return unserialize($data);
      case 'raw':
      default:
        return $data;
    }
  }

  function accept_header($format){
    switch ($format){
      case 'json':
        return 'application/json, */*;q=0.2';
      case 'xml':
      case 'dom':
        return 'application/xml, */*;q=0.2';
      case 'html':
        return 'text/html, */*;q=0.2';        
      case 'rdf':
        return 'application/rdf+xml, */*;q=0.2';
      case 'raw':
      default:
        return '*/*';
    }
  }
  
  function parse_http_response_header(){
    $this->http_headers = array();
    
    $item = array();
    $status = 0;

    foreach ($this->http_response_header as $header){
      if (preg_match('/HTTP\/.+?\s+(\d+)\s+(.+)/', $header, $matches)){
        if ($status)
          $this->save_http_header($status, $item);

        $status = $matches[1];
        $item = array();
        continue;
      }

      preg_match('/(.+?):\s+(.+)/', $header, $matches);
      $item[str_replace('-', '_', strtolower($matches[1]))][] = $matches[2];
    }

    $this->save_http_header($status, $item);
  }
  
  function save_http_header($status, $item){  
    // convert arrays to strings if only one item
    foreach ($item as &$data)
      if (count($data) === 1)
        $data = $data[0];
    
    $item['status'] = $status;
    $this->http_headers[] = $item;
  }
  
  function get_input_dir($dir = ''){
    if (strpos($dir, '/') !== 0) // path doesn't start with '/', so treat as relative to DATA_DIR
      $dir = Config::get('DATA_DIR') . '/' . $dir;
      
    if (!file_exists($dir) || !is_dir($dir))
      return FALSE;
      
    return $dir;
  }

  function get_output_dir($dir = ''){
    #$dir = preg_replace('/[^a-z0-9\(\)\_\-\+ ]/i', '_', $dir); // FIXME: proper sanitising
    
    if (strpos($dir, '/') !== 0) // path doesn't start with '/', so treat as relative to DATA_DIR
      $dir = Config::get('DATA_DIR') . '/' . $dir;
    
    if (!file_exists($dir))
      mkdir($dir, 0777, TRUE); // TRUE = recursive // 0755?
      
    if (!is_dir($dir))
      exit('Could not create output folder ' . $dir);

    return $dir;
  }
  
  function get_latest($args, $default = 1){
    if (isset($args['from']))
      return $args['from'];
    else if ($this->output_dir && file_exists($this->output_dir . '/latest'))
      return file_get_contents($this->output_dir . '/latest');
    else
      return $default;
  }
  
  function base64_encode_file($t){
    return strtr(base64_encode($t), '+/', '-_') ;
  }

  function base64_decode_file($t){
    return base64_decode(strtr($t, '-_', '+/'));
  }
  
  static function o($input, $context = 'html'){
    if (is_integer($input))
      return print $input;
       
    switch ($context){
      case 'raw':
        print $input;
      break;
      
      case 'html':
      default:
        print htmlspecialchars($input, NULL, 'UTF-8'); // FIXME: filter_var + FILTER_SANITIZE_SPECIAL_CHARS?
      break;
      
      case 'attr':
      case 'attribute':
        print htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // FIXME: filter_var + FILTER_SANITIZE_SPECIAL_CHARS?
      break;
    }
  }
  
  function xpath_item($xml, $query){
    $nodes = $xml->xpath($query);
    if (!empty($nodes))
      return (string) $nodes[0];
    return FALSE;
  }

  function xpath_items($xml, $query){
    $nodes = $xml->xpath($query);
    $items = array();
    if (!empty($nodes))
      foreach ($nodes as $node)
        $items[] = (string) $node;
    return $items; 
  }
  
  function opensearch($url, $params){
    $xml = $this->get_data($url, $params, 'xml');

    //debug($xml);

    if (!is_object($xml))
      return FALSE;

    $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
    $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    $xml->registerXPathNamespace('opensearch', 'http://a9.com/-/spec/opensearch/1.1/');

    $meta = array(
      'total' => (int) current($xml->xpath('opensearch:totalResults')),
      'page' => (int) current($xml->xpath('opensearch:startIndex')),
      'items' => (int) current($xml->xpath('opensearch:itemsPerPage')),
      );

    return array($xml, $meta);
  }
  
  function validate(&$args, $required, $default = array()){
    if (is_string($required))
      $required = array($required);
          
    foreach ($default as $key => $value)
      if (!isset($args[$key]))
        $args[$key] = $value;
        
    foreach ($required as $key)
      if (!isset($args[$key]))
        trigger_error(sprintf('Missing required argument "%s"', $key), E_USER_ERROR);
  }
  
  function set_default(&$args, $key, $value){
    if (!isset($args[$key]))
      $args[$key] = $value;
  }
}
