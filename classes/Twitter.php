<?php

class Twitter extends API {
  public $doc = 'http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-user_timeline';
  //public $def = 'TWITTER_AUTH'; // http://apiwiki.twitter.com/Authentication - username:password for basic authentication

  function followers($args){
    $this->validate($args, array('user', 'cursor'), array('cursor' => '-1')); extract($args);

    $json = $this->get_data('http://twitter.com/followers/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));
    $this->cursor = $json->next_cursor;
    return $json->ids;      
  }
  
  function friends($args){
    $this->validate($args, array('user', 'cursor'), array('cursor' => '-1')); extract($args);

    $json = $this->get_data('http://twitter.com/friends/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));
    $this->cursor = $json->next_cursor;
    return $json->ids;      
  }
  
  function user($q){
    if ((!$user = $q['user']) && (!$id = $q['id']))
      return FALSE;
    
    $json = $this->get_data('http://twitter.com/users/show.json', array('screen_name' => $user, 'user_id' => $id,));
    return $json;      
  }

  function content_by_user($args){
    $this->validate($args, array('user', 'max'), array('max' => 3200)); extract($args); // maximum 3200 items available through the API    
    
    if ($output)
      $this->output_dir = $this->get_output_dir($output);

    $auth = explode(':', Config::get('TWITTER_AUTH'));
      
    $from = $this->get_latest($args, 1); // 1 = earliest status id
   
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
  
      debug($json);
    
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
      
      if (($page * $n) >= $max)
       break;
      
      sleep(1);
      $page++;
    } while (!empty($json));

    return $items;
  }
}