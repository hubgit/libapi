<?php

# http://code.google.com/apis/ajaxsearch/documentation/#fonje

return defined('GOOGLE_REFERER');

function search_google($q, $params = array()){
  if (!$q)
    return FALSE;
    
  $default = array(
    'q' => $q,
    'v' => '1.0',
    'rsz' => 'large',    
  );
  
  $http = array('header' => 'Referer: ' . GOOGLE_REFERER);
  $json = get_data('http://ajax.googleapis.com/ajax/services/search/web', array_merge($default, $params), 'json', $http);
  
  //debug($json);
  
  if (!is_object($json))
    return FALSE;
    
  return array((int) $json->responseData->cursor->estimatedResultCount, $json->responseData->results);
}
