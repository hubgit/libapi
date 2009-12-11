<?php

class Amazon extends API {
  function sign($params){
    uksort($params, 'strnatcmp');
    
    $q = array();
    foreach ($params as $key => $val)
        $q[] = $key . '=' . rawurlencode($val);
    $q = implode('&', $q);
    
    //$q = http_build_query($params);
    //debug($q);
    //debug(implode("\n", array('GET', 'ec2.amazonaws.com', '/onca/xml', $q)));
      
    return base64_encode(hash_hmac('sha256', implode("\n", array('GET', 'ec2.amazonaws.com', '/onca/xml', $q)), Config::get('AMAZON_SECRET'), TRUE));
  }
  
  function search($index, $keywords){
    $params = array(
      'Service' => 'AWSECommerceService',
      'Operation' => 'ItemSearch',
      //'Version' => '2009-01-06',
      'AWSAccessKeyId' => Config::get('AMAZON'),
      'AssociateTag' => Config::get('AMAZON_ASSOCIATE'),
      'SearchIndex' => $index,
      'Keywords' => $keywords,
      'Timestamp' => date(DATE_ATOM),
      );
      
    $params['Signature'] = $this->sign($params);
      
    $xml = $this->get_data_curl('http://ecs.amazonaws.com/onca/xml', $params);
  } 
}

