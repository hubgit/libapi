<?php

class Amazon extends API {
  public $host = 'ecs.amazonaws.com';
  public $path = '/onca/xml';
  
  function sign($params){
    uksort($params, 'strnatcmp');
    
    foreach ($params as $key => &$value)
      $value = $key . '=' . rawurlencode($value); // http_build_query uses urlencode rather than rawurlencode

    return base64_encode(hash_hmac('sha256', implode("\n", array('GET', $this->host, $this->path, implode('&', $params))), Config::get('AMAZON_SECRET'), TRUE));
  }
  
  function call($params){
    $default = array(
      'Service' => 'AWSECommerceService',
      'Version' => '2009-10-01',
      'Timestamp' => date(DATE_ISO8601),
      'AWSAccessKeyId' => Config::get('AMAZON'),
      'AssociateTag' => Config::get('AMAZON_ASSOCIATE'),
      );
      
    $params = array_merge($default, $params); 
    $params['Signature'] = $this->sign($params);      
    $xml = $this->get_data('http://' . $this->host . $this->path, $params, 'xml');
    
    if (!is_object($xml) || (string) $xml->Items->Request->IsValid != 'True')
      return FALSE;
      
    return $xml;
  }
  
  function search($params){
    $default = array(
      'Operation' => 'ItemSearch', 
      'ResponseGroup' => 'ItemAttributes',
      );
      
    $xml = $this->call(array_merge($default, $params));
      
    $items = array();
    if (!empty($xml->Items->Item))
      foreach ($xml->Items->Item as $item)
        $items[(string) $item->ASIN] = $item;
      
    return array($items, array('total' => (int) $xml->Items->TotalResults, 'pages' => (int) $xml->Items->TotalPages));
  } 
}
