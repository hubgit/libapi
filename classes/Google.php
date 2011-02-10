<?php

class Google extends API {
  public $def = array(
    'GOOGLE_AUTH', 
    //'GOOGLE_REFERER'
  );
  
  public $token;
  
  function headers($items = array()){
    $default = array(
      'GData-Version' => '3.0',
      'Authorization' => 'GoogleLogin auth=' . $this->token,
      );
      
    $headers = array_merge($default, $items);
          
    foreach ($headers as $key => &$value)
      $value = $key . ': ' . $value;
    
    return implode("\n", $headers);
  }
  
  function authorise($service = 'writely', $source = 'libapi', $account_type = 'GOOGLE'){ // 'writely' = Docs, 'wise' = Spreadsheets, 'xapi' = Prediction
    if ($this->token)
      return;
      
    debug('Authorising');
    $auth = explode(':', Config::get('GOOGLE_AUTH'));
      
    $params = array(
      'Email' => $auth[0], 
      'Passwd' => $auth[1], 
      'service' => $service,
      'source' => $source,
      'accountType' => $account_type,
      );
      
    $http = array('method' => 'POST', 'content' => http_build_query($params));
    $this->get_data('https://www.google.com/accounts/ClientLogin', array(), 'raw', $http);
    
    preg_match('/(?:^|\n)SID=(.+?)\n/s', $this->data, $matches);
    $this->cookie = implode('; ', array(
      'SID=' . $matches[1],
      'domain=.google.com',
      'path=/',
      'expires=160000000000',
      ));
      
    preg_match('/\nAuth=(.+?)\n/s', $this->data, $matches);
    $this->token = $matches[1];
  }
  
  // http://code.google.com/apis/ajaxsearch/documentation/#fonje
  function search($q, $params = array()){      
    $default = array(
      'q' => $q,
      'v' => '1.0',
      'rsz' => 'large',  
      'key' => Config::get('GOOGLE'),  
    );

    $http = array('header' => 'Referer: ' . Config::get('GOOGLE_REFERER'));
    $this->get_data('http://ajax.googleapis.com/ajax/services/search/web', array_merge($default, $params), 'json', $http);

    $this->results = $this->data->responseData->results;
    $this->total = $this->data->responseData->cursor->estimatedResultCount;
    $this->cursor = $this->data->responseData->cursor;
  }

  function mail_feed($label = ''){
    $http = array('header' => 'Authorization: Basic ' . base64_encode(Config::get('GOOGLE_AUTH')));
    return $this->get_data('https://mail.google.com/a/mendeley.com/feed/atom/' . urlencode($label), array(), 'dom', $http);
  }
}
