<?php

# http://opencalais.com/documentation/calais-web-service-api

function categories_wikipedia($q){
  if (!$title = $q['title'])
    return FALSE;
    
  if (is_array($title)) // fetch multiple titles at once
    $title = implode('|', $title);
    
  $json = get_data('http://en.wikipedia.org/w/api.php', array(
    'action' => 'query',
    'format' => 'json',
    'prop' => 'categories',
    'redirects' => 'true',
    'cllimit' => 500,
    'clshow' => '!hidden',
    'titles' => $title,
    ), 'json', $http);
  
  debug($json);
  
  if (!is_object($json))
    return array();
      
  $categories = array();
      
  foreach ($json->query->pages as $id => $data){
    $item = array(
      'id' => $data->pageid,
      'title' => $data->title,
      );
    
    foreach ($data->categories as $category)
      $item['categories'][] = preg_replace('/^Category:/', '', $category->title);
    
    $categories[$data->title] = $item;
  }
  
  return $categories;
}
