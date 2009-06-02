<?php

# http://code.google.com/apis/maps/documentation/geocoding/index.html

return defined('GOOGLE_API_KEY');

function geocode_google($q){
  $json = get_data('http://maps.google.com/maps/geo', array(
    'q' => $q,
    'output' => 'json',
    'oe' => 'utf8',
    'sensor' => 'false',
    'key'=> GOOGLE_API_KEY,
  ));
  
  //debug($json);
  
  if (!is_object($json) || $json->Status->code != 200)
    return array(FALSE, array(FALSE, FALSE));
  
  $place = $json->Placemark[0];
  
  list($lon, $lat, $level) = $place->Point->coordinates;
  
  return array($place->address, array((float) $lat, (float) $lon));
}

