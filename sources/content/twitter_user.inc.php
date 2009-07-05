<?php

# http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-user_timeline

return defined('TWITTER_AUTH');

function content_twitter_user($q){
  if (!$user = $q['user'])
    return FALSE;
    
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
    
  if (isset($q['from']))
    $from = $q['from'];
  else if ($output_folder && file_exists($output_folder . '/latest'))
    $from = file_get_contents($output_folder . '/latest');
  else
    $from = 1; // status id
   
  $n = 200; // max 200
  $page = 1; // pages start at 1
  
  $items = array();
  
  $auth = explode(':', TWITTER_AUTH);
   
  do {
   $json = get_data(
     sprintf('http://%s:%s@twitter.com/statuses/user_timeline.json', urlencode($auth[0]), urlencode($auth[1])), // http://apiwiki.twitter.com/Authentication
     array(
      'screen_name' => $user,
      'since_id' => $from,
      'count' => $n,
      'page' => $page,
      ));
  
    debug($json);
    
    if (!is_array($json) || empty($json))
      return FALSE;
    
    foreach ($json as $item){
      if ($output_folder){
        unset($item->user); // always the same

        $id = preg_replace('/\D/', '', $item->id); // can't use %d as too big        
        $out = sprintf('%s/%s.js', $output_folder, $id);
        file_put_contents($out, json_encode($item));
      }
      else
        $items[] = $item;
    }
    
    if ($output_folder && $page == 1) // always in descending order
      file_put_contents($output_folder . '/latest', $json[0]->id);
  
    sleep(1);
    $page++;
    
  } while (!empty($json));

  return $items;
}
