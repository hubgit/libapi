<?php

# http://api.guardianapis.com/docs

return defined('GUARDIAN_KEY');

function content_guardian($q){
  if (!$query = $q['guardian-filter'])
    return FALSE;
    
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
  
  $n = 50;
  $page = 0; // results start at 0
  
  $daily_limit = 5000;
  
  $items = array();
  
  do{
    $start = $page * $n;
    //print "$start\n";
      
    $json = get_data('http://api.guardianapis.com/content/search', array(
      'api_key' => GUARDIAN_KEY,
      'content-type' => 'article',
      'filter' => $query,
      'format' => 'json',
      'count' => $n,
      'start-index' => $start,
    ));
    
    if (!is_object($json) || empty($json->search->results))
      break;
      
    foreach ($json->search->results as $item){
      if ($output_folder)
        file_put_contents(sprintf('%s/%d.js', $output_folder, $item->id), json_encode($item)); 
      else
        $items[] = $item;
    }

    sleep(1);
  } while ($start < $json->search->count && ++$page < $daily_limit);
  
  return $items;
}

