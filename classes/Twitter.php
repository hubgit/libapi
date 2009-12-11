<?php

class Twitter extends API {
  public $doc = 'http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-user_timeline';
  public $def = 'TWITTER_AUTH'; // http://apiwiki.twitter.com/Authentication - username:password for basic authentication

  function content_by_user($q){
    if (!$user = $q['user'])
      return FALSE;
      
    if (!$max = $q['max'])
      $max = 3200; // maximum 3200 items available through the API
    
    if (isset($q['output']))
      $this->output_dir = $this->get_output_dir($q['output']);

    $auth = explode(':', Config::get('TWITTER_AUTH'));
      
    $from = $this->get_latest($q, 1); // 1 = earliest status id
   
    $n = min($max, 200); // max 200
    $page = 1; // pages start at 1
  
    $items = array();
    $count = 0;
    do {
     $json = $this->get_data(
       sprintf('http://%s:%s@twitter.com/statuses/user_timeline.json', urlencode($auth[0]), urlencode($auth[1])),
       array(
        'screen_name' => $user,
        'since_id' => $from,
        'count' => $n,
        'page' => $page,
        ));
  
      //debug($json);
    
      if (!is_array($json) || empty($json))
        break;
    
      foreach ($json as $item){          
        if ($this->output_dir){
          $out = sprintf('%s/%s.js', $this->output_dir, preg_replace('/\D/', '', $item->id)); // can't use %d as $item->id is too big, so sanitise by removing non-numeric characters
          file_put_contents($out, json_encode($item));
        }
        else
          $items[] = $item;
      }
    
      if ($this->get_output_dir && $page == 1) // always in descending order
        file_put_contents($this->output_dir . '/latest', $json[0]->id);
  
      sleep(1);
      $page++;
    } while (!empty($json));

    return $items;
  }
}