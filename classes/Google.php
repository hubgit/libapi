<?php

class Google extends API {
  public $doc = 'http://reader.google.com/';
  public $def = array('GOOGLE_AUTH', 'GOOGLE_REFERER');
  
  // http://code.google.com/apis/ajaxsearch/documentation/#fonje
  function search_google($q, $params = array()){      
    if (!$q)
      return FALSE;

    $default = array(
      'q' => $q,
      'v' => '1.0',
      'rsz' => 'large',    
    );

    $http = array('header' => 'Referer: ' . GOOGLE_REFERER);
    $json = get_data('http://ajax.googleapis.com/ajax/services/search/web', array_merge($default, $params), 'json', $http);

    //debug($json);

    if (!is_object($json))
      return FALSE;

    return array($json->responseData->results, array('total' => (int) $json->responseData->cursor->estimatedResultCount));
  }
  
  function auth(){
    $auth = explode(':', GOOGLE_AUTH);
  
    $params = array('Email' => $auth[0], 'Passwd' => $auth[1]);
    $context = stream_context_create(array('http' => array('method' => 'POST', 'content' => http_build_query($params))));
    $result = file_get_contents('https://www.google.com/accounts/ClientLogin', NULL, $context);
    $sid = array_pop(explode('=', array_shift(explode("\n", $result))));

    $cookie = array(
      'SID=' . $sid,
      'domain=.google.com',
      'path=/',
      'expires=160000000000',
      );

    return array('header' => sprintf("Cookie: %s\r\n", implode('; ', $cookie))); 
  }

  function content_by_feed($q){
    if (!$query = $q['feed'])
      return FALSE;
    
    if (isset($q['output']))
      $output_dir = output_dir($q['output']);

    $http = $this->auth();
  
    $n = 100;
  
    $items = array();
    $continuation = '';
  
    do{
      $xml = get_data('http://www.google.com/reader/atom/feed/' . urlencode($query), array(
        'n' => $n,
        'c' => $continuation,
        ), 'xml', $http);

      //debug($xml);

      if (!is_object($xml))
        break;
      
      $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
      $xml->registerXPathNamespace('gr', 'http://www.google.com/schemas/reader/atom/');
    
      foreach ($xml->xpath('/atom:feed/atom:entry') as $item){
        if ($output_dir)
          file_put_contents(sprintf('%s/%s.xml', $output_dir, base64_encode($item->id)), $item->asXML()); 
        else
          $items[] = $item;
      }

      $continuation = (string) current($xml->xpath('/atom:feed/gr:continuation'));
      debug('Continuation: ' . $continuation);
    
      //sleep(1);

    } while ($continuation);
  
    return $items;
  }
}
