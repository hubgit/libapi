<?php

class API {
  public $def;
  public $doc;

  public $input_dir;
  public $output_dir;

  public $output_file;

  public $output;

  public $http_response_header;
  public $http_headers;

  public $response;
  public $data;

  public $cache = TRUE;
  public $cache_expire = 86400; //60*60*24; // use the cache file if it's less than one day old

  // SOAP client
  public $soapclient;

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

  function check_def($def){
    if (Config::get($def) === FALSE)
      throw new Exception('Requirement not defined: ' . $def);
  }

  function soap($wsdl, $method){
    $args = func_get_args();
    $params = array_slice($args, 2);
    ksort($params);
    debug($params);
    $key = md5($wsdl . '#' . $method . '?' . http_build_query($params));

    if ($this->cache)
     $this->data = $this->cache_get($key);

    if (is_null($this->data)){
      try{
        $this->soapclient = new SOAPClient($wsdl, array(
          'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
          //'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
        ));
        $this->data = call_user_func_array(array($this->soapclient, $method), $params);
      } catch (SoapFault $exception) { debug($exception); } // FIXME: proper error handling

      if ($this->cache && !is_null($this->data))
        $this->cache_set($key, $this->data);
    }
    else{
      debug('Cached SOAP response');
      debug("Cached:\n" . print_r(array($wsdl, $method, $params), TRUE));
    }

    debug($this->data);
  }

  function cache_set($key, $data = NULL){
    $cache_dir = $this->get_output_dir('cache-uri');
    $cache_file = sprintf('%s/%s.gz', $cache_dir, $key);
    file_put_contents('compress.zlib://' . $cache_file, serialize($data));
  }

  function cache_get($key){
    $cache_dir = $this->get_output_dir('cache-uri');
    $cache_file = sprintf('%s/%s.gz', $cache_dir, $key);
    if (file_exists($cache_file) && ((time() - filemtime($cache_file)) < $this->cache_expire))
      return unserialize(file_get_contents('compress.zlib://' . $cache_file));
  }

  function get_cached_data($url, $params = array(), $format = 'json', $http = array()){
    debug();
    if (!empty($params))
      ksort($params);
    $suffix = empty($params) ? NULL : '?' . http_build_query($params);
    $key = md5($format . ':' . $url . $suffix); // TODO: use Accept header as well as format? Use proper Cache-Control and Vary response headers?

    if ($data = $this->cache_get($key)) {
      debug("Cached: \n" . $format . ' ' . $url . $suffix);
      $this->response = $data['content'];
      $this->http_response_header = $data['header'];
      $this->parse_http_response_header();
    }
    else {
      // set the accept header here, because the format is set to 'raw'
      if (!isset($http['header']) || !preg_match('/Accept: /', $http['header']))
        $http['header'] .= (empty($http['header']) ? '' : "\n") . $this->accept_header($format);

      $this->get_data($url, $params, 'raw', $http, FALSE);
      if ($this->response !== FALSE)
        $this->cache_set($key, array('header' => $this->http_response_header, 'content' => $this->data));
    }

    try {
      $this->data = $this->format_data($format);
      $this->validate_data($format);
    }
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }

    return $this->data;
  }

  function get_data($url, $params = array(), $format = 'json', $http = array(), $cache = TRUE){
    debug();
    if ($cache && $this->cache) // can set either of these to FALSE to disable the cache
      if (!isset($http['method']) || $http['method'] == 'GET') // only use the cache for GET requests (TODO: allow caching of some POST requests?)
        return $this->get_cached_data($url, $params, $format, $http);

    // FIXME: is this a good idea?
    if ($http['method'] == 'POST' && empty($http['content']) && !empty($params)){
      $http['content'] = http_build_query($params);
      $params = array();
    }

    if (!empty($params)){
      ksort($params);
      $url .= '?' . http_build_query($params);
    }

    if (isset($http['file']))
      $http['content'] = file_get_contents($http['file']);

    // TODO: allow setting default HTTP headers in Config.php

    if (!isset($http['header']) || !preg_match('/Accept: /', $http['header']))
      $http['header'] .= (empty($http['header']) ? '' : "\n") . $this->accept_header($format);

    debug($http);

    $context = empty($http) ? NULL : stream_context_create(array('http' => $http));
    
    if (!empty($this->oauth)){
      $oauth = new OAuth($this->oauth['consumer_key'], $this->oauth['consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
      $oauth->enableDebug();
      $oauth->setToken($this->oauth['token'], $this->oauth['secret']);
      try {
        debug($url);
        $this->response = $oauth->fetch($url);
        $info = $oauth->getLastResponseInfo();
        debug($info);
        $this->http_response_header = explode("\n", $info['headers_recv']);
        debug($this->http_response_header);
      } catch (OAuthException $e) { debug($oauth->debugInfo); }
    }
    else {
      debug('Sending request to ' . $url);
      $this->response = file_get_contents($url, NULL, $context);
      debug('Received response');
      debug($http_response_header);
      $this->http_response_header = $http_response_header;
    }
    
    $this->parse_http_response_header();    
    debug($this->response);

    try {
      $this->data = $this->format_data($format);
      $this->validate_data($format);
    }
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }

    return $this->data;
  }

  function get_data_curl($url, $params = array(), $format = 'json', $http = array(), $curl_params = array()){
    debug($params);
    if (!empty($params))
      $url .= '?' . http_build_query($params);

    $curl = curl_init($url);
    debug($url);
    debug($http);

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

    //debug($this->response);
    debug('Status: ' . $this->http_status);
    file_put_contents(sys_get_temp_dir() . '/raw.xml', $this->response);

    curl_close($curl);
    if (isset($http['file']))
      fclose($http['file']);

    try {
      $this->data = $this->format_data($format);
      $this->validate_data($format);
    }
    catch (DataException $e) { $e->errorMessage(); }
    catch (Exception $e) { debug($e->getMessage()); }

    return $this->data;
  }

  function format_data($format){
    switch ($format){
      case 'json':
      return json_decode($this->response);
      case 'xml':
      return simplexml_load_string($this->response, NULL, LIBXML_NOCDATA | LIBXML_NONET);
      case 'dom':
      $dom = new DOMDocument;
      $dom->preserveWhiteSpace = FALSE;
      $dom->loadXML($this->response, LIBXML_DTDLOAD | LIBXML_DTDVALID | LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET);
      $dom->encoding = 'UTF-8';
      $dom->formatOutput = TRUE;
      if (is_object($dom))
       $this->xpath = new DOMXPath($dom);
      return $dom;
      case 'html':
      return simplexml_import_dom($this->format_data('html-dom'));
      case 'html-dom':
      $dom = @DOMDocument::loadHTML($this->response);
      if (is_object($dom))
        $this->xpath = new DOMXPath($dom);
      return $dom;
      case 'rdf-xml':
      return DOMDocument::loadXML($this->response, LIBXML_DTDLOAD | LIBXML_DTDVALID | LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET); // FIXME: need proper RDF parser
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
      return 'Accept: application/json,*/*;q=0.2';
      case 'xml':
      case 'dom':
      return 'Accept: application/xml,*/*;q=0.2';
      case 'html':
      return 'Accept: text/html,*/*;q=0.2';
      case 'rdf':
      return 'Accept: application/rdf+xml,*/*;q=0.2';
      case 'raw':
      default:
      return 'Accept: */*';
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

  function get_latest($from = NULL, $default = 1){
    if ($from)
      return $from;
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

  function opensearch_json($url, $params){
    $this->get_data($url, $params, 'json');

    $this->total = $this->data->feed->{'opensearch:totalResults'};
    $this->page = $this->data->feed->{'opensearch:startIndex'};
    $this->itemsPerPage = $this->data->feed->{'opensearch:itemsPerPage'};
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

