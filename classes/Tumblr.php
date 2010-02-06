<?php

class Tumblr extends API {
  public $doc = 'http://www.tumblr.com/docs/api';
  //public $def = 'TUMBLR_AUTH'; // http://www.tumblr.com/docs/api#authenticate

  function content_by_user($q){
    if (!$user = $q['user'])
      return FALSE;
    
    $this->output_dir = isset($q['output']) ? $this->get_output_dir($q['output']) : NULL;

    $items = array();
    $count = 0;
    
    $params = array(
      'num' => 50,
      'start' => 0, // item offset
      );
    
    do {
      debug(sprintf('Fetching items %d to %d of %d', $params['start'], $params['start'] + $params['num'], (int) $xml->posts['total']));
      $xml = $this->get_data(sprintf('http://%s.tumblr.com/api/read', $user), $params, 'xml');
      
      $total = (int) $xml->posts['total'];
      $max = isset($q['max']) ? min($q['max'], $total) : $total;
  
      //debug($xml);
       
      //if (!is_object($xml) || !is_array($xml->posts->post) || empty($xml->posts->post))
        //break;
          
      foreach ($xml->posts->post as $item){ 
        debug($item);
        if ($this->output_dir){
          // can't use %d in case $item->id is too big, so sanitise by removing non-numeric characters
          $out = sprintf('%s/%s.js', $this->output_dir, preg_replace('/\D/', '', (int) $item['id'])); 
          file_put_contents($out, json_encode($item));
        }
        else
          $items[] = $item;
      }
    
      $params['start'] += $params['num'];
      sleep(1);
      
    } while ($params['start'] < $max);

    return $items;
  }
}