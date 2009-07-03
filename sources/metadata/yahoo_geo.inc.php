<?php

# http://developer.yahoo.com/geo/geoplanet/

return defined('YAHOO_KEY');

function metadata_yahoo_geo($q){ 
  if (!$id = $q['woeid'])
   return FALSE;
    
  $json = get_data('http://where.yahooapis.com/v1/place/' . $id, array(
    'appid' => YAHOO_KEY,
    'format' => 'json',
    ));
  
  debug($json);
  
  if (!is_object($json))
    return FALSE;
    
  return $json->place;
}
