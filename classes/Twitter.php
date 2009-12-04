<?php

class Twitter extends API {
  public $doc = 'http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-user_timeline';
  public $def = 'TWITTER_AUTH'; // http://apiwiki.twitter.com/Authentication

  function content_by_user($q){
    if (!$user = $q['user'])
      return FALSE;
    
    if (isset($q['output']))
      $output_dir = output_dir($q['output']);
    
    if (isset($q['from']))
      $from = $q['from'];
    else if ($output_dir && file_exists($output_dir . '/latest'))
      $from = file_get_contents($output_dir . '/latest');
    else
      $from = 1; // status id
   
    $n = 200; // max 200
    $page = 1; // pages start at 1
  
    $items = array();
  
    $auth = explode(':', Config::get('TWITTER_AUTH'));
   
    do {
     $json =$this->get_data(
       sprintf('http://%s:%s@twitter.com/statuses/user_timeline.json', urlencode($auth[0]), urlencode($auth[1])),
       array(
        'screen_name' => $user,
        'since_id' => $from,
        'count' => $n,
        'page' => $page,
        ));
  
      //debug($json);
    
      if (!is_array($json) || empty($json))
        return FALSE;
    
      foreach ($json as $item){
        if ($output_dir){
          //unset($item->user); // 'user' property is always the same
          $id = preg_replace('/\D/', '', $item->id); // can't use %d as too big, so sanitise by removing non-numeric characters     
          $out = sprintf('%s/%s.js', $output_dir, $id);
          file_put_contents($out, json_encode($item));
        }
        else
          $items[] = $item;
      }
    
      if ($output_dir && $page == 1) // always in descending order
        file_put_contents($output_dir . '/latest', $json[0]->id);
  
      sleep(1);
      $page++;
    
    } while (!empty($json));

    return $items;
  }
}