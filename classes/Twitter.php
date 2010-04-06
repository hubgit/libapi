<?php

class Twitter extends API {
  public $doc = 'http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-user_timeline';
  //public $def = 'TWITTER_AUTH'; // http://apiwiki.twitter.com/Authentication - username:password for basic authentication
  
  public $results = array();

  function followers($user, $id = NULL, $cursor = -1){
    $this->get_data('http://twitter.com/followers/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));
    $this->cursor = $this->data->next_cursor;
    $this->results = $this->data->ids;
  }
  
  function friends($user, $id = NULL, $cursor = -1){
    $this->get_data('http://twitter.com/friends/ids.json', array('screen_name' => $user, 'user_id' => $id, 'cursor' => $cursor));
    $this->cursor = $this->data->next_cursor;
    $this->results = $this->data->ids;
  }
  
  function user($user, $id = NULL){
    $this->get_data('http://twitter.com/users/show.json', array('screen_name' => $user, 'user_id' => $id));
  }

  // maximum 3200 items available through the API 
  function content_by_user($user, $max = 3200){ 
    $auth = explode(':', Config::get('TWITTER_AUTH'));
      
    $from = $this->get_latest($args, 1); // 1 = earliest status id
   
    $n = min($max, 200); // max 200
    $page = 1; // pages start at 1
  
    $count = 0;
    do {
     $this->get_data(
       sprintf('http://%s:%s@twitter.com/statuses/user_timeline.json', urlencode($auth[0]), urlencode($auth[1])),
       array(
        'screen_name' => $user,
        'since_id' => $from,
        'count' => $n,
        'page' => $page,
        ));
      
      if (!is_array($this->data) || empty($this->data))
        break;
    
      foreach ($this->data as $item){          
        if ($this->output_dir){
          $out = sprintf('%s/%s.js', $this->output_dir, preg_replace('/\D/', '', $item->id)); // can't use %d as $item->id is too big, so sanitise by removing non-numeric characters
          file_put_contents($out, json_encode($item));
        }
        else
          $this->results[] = $item;
      }
    
      if ($this->output_dir && $page == 1) // always in descending order
        file_put_contents($this->output_dir . '/latest', $this->data[0]->id);
      
      if (($page * $n) >= $max)
        break;
      
      sleep(1);
      $page++;
    } while (!empty($this->data));
  }
}