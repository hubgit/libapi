<?php

#

return defined('YAHOO_KEY');

function search_boss($q, $params = array()){
  if (!$q)
    return FALSE;
    
  $default = array(
    'view' => 'language,delicious_toptags,delicious_saves,keyterms,searchmonkey_feed',
    'abstract' => 'long',
    'format' => 'json',
    'appid' => YAHOO_KEY,
  );
    
  $json = get_data('http://boss.yahooapis.com/ysearch/web/v1/' . urlencode($q), array_merge($default, $params));
  
  debug($json);
  
  if (!is_object($json))
    return FALSE;
    
  return array((int) $json->ysearchresponse->totalhits, $json->ysearchresponse->resultset_web);
}
