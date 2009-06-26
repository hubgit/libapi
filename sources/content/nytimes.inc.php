<?php

# http://developer.nytimes.com/docs/article_search_api

return defined('NYTIMES_KEY');

function content_nytimes($q){
  if (isset($q['nytimes-facet']))
    $query = sprintf('des_facet:[%s]', $q['nytimes-facet']);
    
  if (!isset($query))
    return FALSE;
    
  if (isset($q['output']))
    if (!$output_folder = output_folder($q['output']))
      return FALSE;
  
  $n = 10;
  $page = 0; // results start at 0
  
  $daily_limit = 5000;
  
  $items = array();
  
  do{
    $start = $page * $n;
    //print "$start\n";
      
    $json = get_data('http://api.nytimes.com/svc/search/v1/article', array(
      'query' => $query,
      'fields' => 'byline,body,date,title,url,des_facet',
      'api-key' => NYTIMES_KEY,
      'offset' => $page,
    ));
    
    if (!is_object($json) || empty($json->results))
      break;
      
    foreach ($json->results as $item){
      if ($output_folder)
        file_put_contents(sprintf('%s/%s.js', $output_folder, base64_encode($item->url)), json_encode($item)); 
      else
        $items[] = $item;
    }

    sleep(1);
  } while ($start < $json->total && ++$page < $daily_limit);
  
  return $items;
}

