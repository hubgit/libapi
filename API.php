<?php

class API {
  public $input_dir;
  public $output_dir;

  public $output;

  public $http_response_header;
  public $http_headers;

  public $response;
  public $data;

  public $cache = TRUE;
  public $cache_expire = 86400; //60*60*24; // use the cache file if it's less than one day old
  
  // for general use and searches
  public $results = array();
  
  // for searches
  public $total;
  public $pages;
  
  // for entity extraction
  public $annotations = array();
  public $entities = array();
  

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

    $file = sprintf('%s/extlib/%s.php', LIBAPI_ROOT, $class);
    if (file_exists($file))
      return require_once($file);
  }

  function check_def($def){
    if (Config::get($def) === FALSE)
      throw new Exception('Requirement not defined: ' . $def);
  }

  function soap($wsdl, $method, $params){
    ksort($params);
    $key = $wsdl . '#' . $method . '?' . http_build_query($params);

    if (is_null($this->data = $this->cache_get($key))){
      $client = new SOAPClient($wsdl);      
      $this->data = $client->$method($params);

      if (!is_null($this->data))
        $this->cache_set($key, $this->data);
    }
    return $this->data;
  }
  
  function cache_set($key, $data = NULL){
    $cache_dir = $this->get_output_dir('cache-uri');
    $cache_file = sprintf('%s/%s', $cache_dir, $key);
    file_put_contents('compress.zlib://' . $cache_file, serialize($data));
  }
  
  function cache_get($key){
    $cache_dir = $this->get_output_dir('cache-uri');
    $cache_file = sprintf('%s/%s', $cache_dir, $key);
    if (file_exists($cache_file) && ((time() - filemtime($cache_file)) < $this->cache_expire))
      return unserialize(file_get_contents('compress.zlib://' . $cache_file));
  }

  function get_cached_data($url, $params = array(), $format = 'json', $http = array()){
    if (!empty($params))
      ksort($params);
    $suffix = empty($params) ? NULL : '?' . http_build_query($params);
    $key = md5($url . $suffix);

    if ($data = $this->cache_get($key)) {
      debug("Cached: \n" . $url . $suffix);
      $this->response = $data['content'];
      $this->http_response_header = $data['header'];
      $this->parse_http_response_header();
    }
    else {
      $cache_tmp = $this->cache;
      $this->cache = FALSE;
      $this->get_data($url, $params, 'raw', $http);
      $this->cache = $cache_tmp;

      if ($this->response !== FALSE)
        $this->cache_set($key, array('header' => $this->http_response_header, 'content' => $this->data));
    }

    try {
      $this->data = $this->format_data($format, $this->response);
      $this->validate_data($format);
    } 
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }
  }

  function get_data($url, $params = array(), $format = 'json', $http = array()){
    if ($this->cache)
      if (!isset($http['method']) || $http['method'] == 'GET')
        return $this->get_cached_data($url, $params, $format, $http);

    debug($params);
    
    if (!empty($params))
      ksort($params);
    $url .= empty($params) ? NULL : '?' . http_build_query($params);

    debug($url);
    debug($http);

    if (isset($http['file']))
      $http['content'] = file_get_contents($http['file']);

    // TODO: set HTTP Accept headers according to format?
    // TODO: allow setting default HTTP headers in Config.php

    $context = empty($http) ? NULL : stream_context_create(array('http' => $http));

    $this->response = file_get_contents($url, NULL, $context);

    debug($http_response_header);
    debug($this->response);

    $this->http_response_header = $http_response_header;
    $this->parse_http_response_header();

    try {
      $this->data = $this->format_data($format, $this->response);
      $this->validate_data($format);
    } 
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }
  }

  function get_data_curl($url, $params = array(), $format = 'json', $http = array(), $curl_params = array()){  
    debug($params);
    if (!empty($params))
      $url .= '?' . http_build_query($params);

    $curl = curl_init($url);
    debug($url);

    // array_merge doesn't preserve numeric keys
    curl_setopt_array($curl, array(
      CURLOPT_CONNECTTIMEOUT => 60, // 1 minute
    CURLOPT_TIMEOUT => 60*60*24, // 1 day
    CURLOPT_RETURNTRANSFER => TRUE, // return contents
    //CURLOPT_FAILONERROR => TRUE,
    //CURLOPT_SSL_VERIFYPEER => FALSE, // FIXME: temporary fix for curl without SSL certificates
    ) + $curl_params);

    if (isset($http['header']))
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($http['header']));

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

    $this->response = curl_exec($curl);  
    $this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $this->http_info = array(curl_getinfo($curl));

    debug($this->response);
    debug('Status: ' . $this->http_status);  

    curl_close($curl);
    if (isset($http['file']))
      fclose($http['file']);

    try {
      $this->data = $this->format_data($format, $this->response);
      $this->validate_data($format);
    } 
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }
  }

  function format_data($format){
    switch ($format){
      case 'json':
      return json_decode($this->response);
      case 'xml':
      return simplexml_load_string($this->response, NULL, LIBXML_NOCDATA | LIBXML_NONET);
      case 'dom':
      $dom = DOMDocument::loadXML($this->response, LIBXML_DTDLOAD | LIBXML_DTDVALID | LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET);
      $this->xpath = new DOMXPath($dom);
      return $dom;
      case 'html':
      return simplexml_import_dom(@DOMDocument::loadHTML($this->response, LIBXML_NOCDATA | LIBXML_NONET));
      case 'html-dom':
      return @DOMDocument::loadHTML($this->response);
      case 'rdf':
      return DOMDocument::loadXML($this->response, NULL, LIBXML_NOCDATA | LIBXML_NONET); // TODO: parse RDF
      case 'php':
      return unserialize($this->response);
      case 'xmlrpc':
      return xmlrpc_decode($this->response);
      case 'raw':
      default:
      return $this->response;
    }
  }

  function validate_data($format){
    switch ($format){
      case 'xml':
      case 'dom':
      case 'html':
      case 'html-dom':
      case 'rdf':
      if (!is_object($this->data))
        throw new DataException('No XML object');
      break;

      case 'json':
      if (!(is_object($this->data) || is_array($this->data)))
        throw new DataException('No JSON object or array');
      break;

      default:
      if (is_null($this->data))
        throw new DataException('Data is NULL');
      break;
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

    $h = explode(' ', $this->http_response_header[0], 3);
    $this->http_status = $h[1];
    debug('Status: ' . $this->http_status);
  }

  function save_http_header($status, $item){  
    // convert arrays to strings if only one item
    foreach ($item as &$data)
      if (count($data) === 1)
        $data = $data[0];

    $item['status'] = $status;
    $this->http_headers[] = $item;
  }

  static function get_input_dir($dir = ''){
    if (strpos($dir, '/') !== 0) // path doesn't start with '/', so treat as relative to DATA_DIR
      $dir = Config::get('DATA_DIR') . '/' . $dir;

    if (!file_exists($dir) || !is_dir($dir))
      return FALSE;

    return $dir;
  }

  static function get_output_dir($dir = ''){
    //$dir = preg_replace('/[^a-z0-9\(\)\_\-\+ ]/i', '_', $dir); // FIXME: proper sanitising

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

  function opensearch($url, $params){
    $this->get_data($url, $params, 'dom');

    $this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $this->xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    $this->xpath->registerNamespace('opensearch', 'http://a9.com/-/spec/opensearch/1.1/');

    $this->total = $this->xpath->query('opensearch:totalResults')->item(0)->textContent;
    $this->page = $this->xpath->query('opensearch:startIndex')->item(0)->textContent;
    $this->itemsPerPage = $this->xpath->query('opensearch:itemsPerPage')->item(0)->textContent;
  }

  /*
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
  */
}
