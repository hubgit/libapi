<?php

class API {
  function __construct(){
    if (isset($this->def) && !empty($this->def))
      if (is_array($this->def))
        foreach ($this->def as $def)
          $this->check_def($def);
      else
        $this->check_def($def);
  }
  
  static function __autoload($class){
    $file = sprintf('%s/classes/%s', LIBAPI_ROOT, $class);
    if (file_exists($file . '.private.php'))
      require_once($file . '.private.php');
    else if (file_exists($file . '.php'))
      require_once($file . '.php');
  }
  
  function check_def($def){
    if (Config::get($def) === FALSE)
      throw new Exception('Requirement not defined: ' . $def);
  }
  
  function get_data($url, $params = array(), $format = 'json', $http = array()){
    debug($params);
    if (!empty($params))
      $url .= '?' . http_build_query($params);

    debug($url);

    //$http['header'] .= (empty($http['header']) ? '' : "\n") . 'Accept: ' . accept_header($format);
    $context = empty($http) ? NULL : stream_context_create(array('http' => $http));

    $data = file_get_contents($url, NULL, $context);
    //debug($data);
    //debug($http_response_header);

    global $http_status;
    $h = explode(' ', $http_response_header[0], 3);
    $http_status = $h[1];
    debug($http_status);

    return $this->format_data($format, $data);
  }

  function get_data_curl($url, $params = array(), $format = 'json', $http = array(), $curl_params = array()){
    debug($params);
    if (!empty($params))
      $url .= '?' . http_build_query($params);

    $curl = curl_init($url);

    // array_merge doesn't preserve numeric keys
    curl_setopt_array($curl, $curl_params + array(
      CURLOPT_CONNECTTIMEOUT => 60, // 1 minute
      CURLOPT_TIMEOUT => 60*60*24, // 1 day
      CURLOPT_RETURNTRANSFER => 1, // return contents
    ));

    if (isset($http['header']))
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($http['header']));

    $data = curl_exec($curl);  
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    debug('Status: ' . $status);  
    //debug($data);

    curl_close($curl);
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
       return 'application/xml, */*;q=0.2';
      case 'rdf':
        return 'application/rdf+xml, */*;q=0.2';
      case 'raw':
      default:
        return '*/*';
    }
  }
  
  function input_dir($dir){
    $dir = DATA_DIR . $dir;
    if (!file_exists($dir) || !is_dir($dir))
      return FALSE;
    return $dir;
  }

  function output_dir($dir){
    #$dir = preg_replace('/[^a-z0-9\(\)\_\-\+ ]/i', '_', $dir); // FIXME: proper sanitising
    
    if (strpos($dir, '/') !== 0) // path doesn't start with '/', so treat as relative to DATA_DIR
      $dir = DATA_DIR . $dir;

    if (!file_exists($dir))
      mkdir($dir, 0755, TRUE); // TRUE = recursive
    if (!is_dir($dir))
      exit('Could not create output folder ' . $dir);

    return $dir;
  }
  
  function base64_encode_file($t){
    return strtr(base64_encode($t), '+/', '-_') ;
  }

  function base64_decode_file($t){
    return base64_decode(strtr($t, '-_', '+/'));
  }
  
  function output($input, $context = 'html'){
    switch($context){
      case 'raw':
        print $input;
      break;

      case 'html':
      default:
        print htmlspecialchars($input, NULL, 'UTF-8');
      break;

      case 'attribute':
      case 'attr':
        print htmlspecialchars($input, NULL, 'UTF-8');
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
}