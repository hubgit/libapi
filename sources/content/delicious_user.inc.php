<?php

# http://delicious.com/help/api

return defined('DELICIOUS_AUTH');

function content_delicious_user($q){
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
    
  if (isset($q['from']))
    $from = $q['from'];
  else if (file_exists($output_folder . '/latest'))
    $from = file_get_contents($output_folder . '/latest');
  else
    $from = 0; // 1970-01-01T00:00:00Z

  $auth = explode(':', DELICIOUS_AUTH);

  $xml = get_data(
    sprintf('https://%s:%s@api.del.icio.us/v1/posts/all', urlencode($auth[0]), urlencode($auth[1])), 
    array(
      'fromdt' => gmdate("Y-m-d\TH:i:s\Z", $from),
      //'results' => 5,
      ),
    'xml');

  //debug($xml);
       
  if (!is_object($xml) || !isset($xml->post))
    return FALSE;
  
  $items = array();
  
  foreach ($xml->post as $item){
    if ($output_folder)
      file_put_contents(sprintf('%s/%s.xml', $output_folder, preg_replace('/[^a-z0-9]/i', '', (string) $item['hash'])), $item->asXML()); 
    else
      $items[] = $item;
  }
  
  if ($output_folder)
    file_put_contents($output_folder . '/latest', strtotime((string) $xml['update']));

  return $items;
}
