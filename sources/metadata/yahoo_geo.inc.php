<?php

# http://developer.yahoo.com/geo/geoplanet/

return defined('YAHOO_KEY');

function metadata_yahoo_geo($q){ 
  if (!$id = $q['woeid'])
   return FALSE;
  
  $suffix = isset($q['suffix']) ? '/' . $q['suffix'] : '';
    
  $json = get_data('http://where.yahooapis.com/v1/place/' . $id . $suffix, array(
    'appid' => YAHOO_KEY,
    'format' => 'json',
    ));
  
  //debug($json);
  
  if (!is_object($json))
    return FALSE;
  
  return isset($json->places) ? $json->places : $json->place;
}
