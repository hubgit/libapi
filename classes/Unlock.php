<?php

class Unlock extends API {
  public $doc = 'http://unlock.edina.ac.uk:81/service-results/';
  public $def = 'UNLOCK';
  
  public $entities = array();
  public $references = array();

  function entities($text){        
    $boundary = '---------------------' . substr(md5(time()), 0, 10);
    
    $params = array(
      'type' => 'plain',
      'gazetteer' => 'geonames',
      'outputFormat' => 'basic',
      'apiKey' => Config::get('UNLOCK'),
      'document' => $text,
    );
  
    //$b64_text = chunk_split(base64_encode($text));
  
    /*
    $data = "--$boundary\n";
    foreach ($params as $key => $value)
      $data .= "Content-Disposition: form-data; name='$key'\n\n$value\n--$boundary\n";

    $data .= "Content-Disposition: form-data; name='document'; filename='news.txt'\nContent-Type: text/plain\n\n$text\n--$boundary--\n\n";

    $data = str_replace("\n", "\r\n", $data);
  
    $http = array('method' => 'POST', 'content' => $data, 'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary);
    */

    debug($params);
  
    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $xml = $this->get_data('http://unlock.edina.ac.uk:81/service-results/', array(), 'xml', $http);
  
    //debug($xml);
    
    // TODO: parse response
 
  }
}