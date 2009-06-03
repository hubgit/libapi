<?php

#

function search_wikipedia($q, $params = array()){
  if (!$q)
    return FALSE;
    
  $default = array(
    'action' => 'opensearch',
    'format' => 'json',  
    'redirects' => 'true',
    'limit' => 10,
    'search' => $q,
  );
  
  $json = get_data('http://en.wikipedia.org/w/api.php', array_merge($default, $params), 'json', $http);
  
  //debug($json);
  
  if (!is_array($json))
    return FALSE;
    
  return $json[1];
}
