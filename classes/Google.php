<?php

class Google extends API {
  public $def = array('GOOGLE_AUTH', 'GOOGLE_REFERER');
  
  function authorise($service = 'wise', $source = 'libapi', $account_type = 'GOOGLE'){ // 'wise' = Google Docs
    $auth = explode(':', Config::get('GOOGLE_AUTH'));
  
    $params = array(
      'Email' => $auth[0], 
      'Passwd' => $auth[1], 
      'service' => $service,
      'source' => $soure,
      'accountType' => $account_type);
      
    $http = array('method' => 'POST', 'content' => http_build_query($params));
    $result = $this->get_data_curl('https://www.google.com/accounts/ClientLogin', array(), 'raw', $http);
    
    preg_match('/(?:^|\n)SID=(.+?)\n/s', $result, $matches);
    $this->cookie = implode('; ', array(
      'SID=' . $matches[1],
      'domain=.google.com',
      'path=/',
      'expires=160000000000',
      ));
      
    preg_match('/\nAuth=(.+?)\n/s', $result, $matches);
    $this->token = $matches[1];
  }
  
  // http://code.google.com/apis/ajaxsearch/documentation/#fonje
  function search($q, $params = array()){      
    if (!$q)
      return FALSE;

    $default = array(
      'q' => $q,
      'v' => '1.0',
      'rsz' => 'large',    
    );

    $http = array('header' => 'Referer: ' . Config::get('GOOGLE_REFERER'));
    $json = $this->get_data('http://ajax.googleapis.com/ajax/services/search/web', array_merge($default, $params), 'json', $http);

    //debug($json);

    if (!is_object($json))
      return FALSE;

    return array($json->responseData->results, array('total' => (int) $json->responseData->cursor->estimatedResultCount));
  }

  
}
