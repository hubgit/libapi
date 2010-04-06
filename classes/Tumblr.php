<?php

class Tumblr extends API {
  public $doc = 'http://www.tumblr.com/docs/api';
  //public $def = 'TUMBLR_AUTH'; // http://www.tumblr.com/docs/api#authenticate
  public $results = array();
  
  function content_by_user($user, $max = 0){
    $count = 0;
    
    $params = array(
      'num' => 50,
      'start' => 0, // item offset
      );
    
    do {
      $this->get_data(sprintf('http://%s.tumblr.com/api/read', $user), $params, 'xml');
      
      $total = (int) $this->data->posts['total'];
      $max = $max ? min($max, $total) : $total;
          
      foreach ($this->data->posts->post as $item){ 
        if ($this->output_dir){
          // can't use %d in case $item->id is too big, so sanitise by removing non-numeric characters
          $out = sprintf('%s/%s.js', $this->output_dir, preg_replace('/\D/', '', (int) $item['id'])); 
          file_put_contents($out, json_encode($item));
        }
        else
          $this->results[] = $item;
      }
    
      $params['start'] += $params['num'];
      sleep(1);
      
    } while ($params['start'] < $max);
  }
}