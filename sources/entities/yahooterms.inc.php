<?php

#

return defined('YAHOO_KEY');

function entities_yahooterms($q, $query = NULL){
  if (!$text = $q['text'])
    return FALSE;
    
  $params = array(
    'context' => $text,
    'query' => $query, // context for extraction (search terms)
    'output' => 'json',
    'appid' => YAHOO_KEY,
  );
  
  $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
  $json = get_data('http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction', array(), 'json', $http);
  
  debug($json);
  
  if (!is_object($json))
    return array();
    
  return array($json->ResultSet->Result);
}
