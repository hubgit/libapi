<?php

# http://api.guardianapis.com/docs

return defined('GUARDIAN_KEY');

function search_guardian($q, $params = array()){
  if (!$q)
    return FALSE;

  $default = array(
    'api_key' => GUARDIAN_KEY,
    'content-type' => 'article',
    'format' => 'json',
    'q' => $q,
  );
  
  $json = get_data('http://api.guardianapis.com/content/search', array_merge($default, $params));
  
  if (!is_object($json))
    return FALSE;
    
  return array($json->search->count, $json->search->results);
}

