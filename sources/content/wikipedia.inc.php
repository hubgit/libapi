<?php

# http://en.wikipedia.org/w/api.php

function fetch_wikipedia($q){
  if (!$title = $q['title'])
    return FALSE;
    
  $json = get_data('http://en.wikipedia.org/w/api.php', array(
    'action' => 'parse',
    'format' => 'json',
    'redirects' => 'true',
    'prop' => 'text|categories|displaytitle',
    'page' => $title,
  ));
  
  //debug($json);
  
  if (!is_object($json) || !$json->parse->text)
    return array();

  $categories = array();
  foreach ($json->parse->categories as $category)
    $categories[] = $category->{'*'};
  
  return array(
    'title' => $json->parse->displaytitle, 
    'html' => $json->parse->text->{'*'}, 
    'categories' => $categories
    );
}

