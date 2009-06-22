<?php

# http://reader.google.com/

return defined('GOOGLE_AUTH');

function content_google_reader($q){
  if (isset($q['feed-url']))
    $query = $q['feed-url'];
    
  if (!isset($query))
    return FALSE;
    
  if (isset($q['output'])){
    $output_folder = $q['output'];
    if (!file_exists($output_folder))
      mkdir($output_folder, 0755, TRUE);
    if (!is_dir($output_folder))
      return FALSE;
  }

  $http = google_reader_auth();
  
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
      if ($output_folder)
        file_put_contents(sprintf('%s/%s.xml', $output_folder, base64_encode($item->id)), $item->asXML()); 
      else
        $items[] = $item;
    }

    $continuation = (string) current($xml->xpath('/atom:feed/gr:continuation'));
    debug('Continuation: ' . $continuation);
    
    //sleep(1);

  } while ($continuation);
  
  return $items;
}

function google_reader_auth(){
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

