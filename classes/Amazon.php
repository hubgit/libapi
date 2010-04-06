<?php

class Amazon extends API {
  public $host = 'ecs.amazonaws.com';
  public $path = '/onca/xml';
  
  public $results = array();
  public $total;
  public $pages;
  
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
    $this->get_data('http://' . $this->host . $this->path, $params, 'xml');
    
    if ((string) $this->data->Items->Request->IsValid != 'True')
      return FALSE;
  }
  
  function search($params){
    $default = array(
      'Operation' => 'ItemSearch', 
      'ResponseGroup' => 'ItemAttributes',
      );
      
    $this->call(array_merge($default, $params));
      
    if (!empty($this->data->Items->Item))
      foreach ($this->data->Items->Item as $item)
        $this->results[(string) $item->ASIN] = $item;
        
    $this->total = (int) $this->data->Items->TotalResults;
    $this->pages = (int) $this->data->Items->TotalPages;       
  } 
}
