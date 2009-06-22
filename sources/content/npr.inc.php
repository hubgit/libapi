<?php

# http://www.npr.org/api/

return defined('NPR_KEY');

function content_npr($q){
  if (isset($q['npr-topic']))
    $query = $q['npr-topic']; // eg 1007 = 'Health & Science' // http://www.npr.org/api/mappingCodes.php
    
  if (!isset($query) || !is_numeric($query))
    return FALSE;
    
  if (isset($q['output'])){
    $output_folder = $q['output'];
    if (!file_exists($output_folder))
      mkdir($output_folder, 0755, TRUE);
    if (!is_dir($output_folder))
      return FALSE;
  }
  
  $n = 20;
  $page = 0; // results start at 0
    
  $items = array();
  
  do{
    $start = $page * $n;
    //print "$start\n";
      
    $json = get_data('http://api.npr.org/query', array(
      'id' => $query,
      'fields' => 'title,storyDate,text',
      'numResults' => $n,
      'startNum' => ($n * $page) + 1,
      'apiKey' => NPR_KEY,
      'output' => 'JSON',
    ));
    
    //debug($json); exit();
    
    if (!is_object($json) || empty($json->list->story))
      break;
      
    foreach ($json->list->story as $item){
      if ($output_folder)
        file_put_contents(sprintf('%s/%d.js', $output_folder, $item->id), json_encode($item)); 
      else
        $items[] = $item;
    }
    
    $page++;

    //sleep(1);
  } while (1);
  
  return $items;
}

