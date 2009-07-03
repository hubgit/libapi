<?php

# http://code.google.com/apis/maps/documentation/geocoding/index.html

return defined('GOOGLE_MAPS_KEY');

function geocode_google($q){
  $json = get_data('http://maps.google.com/maps/geo', array(
    'q' => $q,
    'output' => 'json',
    'oe' => 'utf8',
    'sensor' => 'false',
    'key' => GOOGLE_MAPS_KEY,
  ));
  
  debug($json);
  
  if (!is_object($json) || $json->Status->code != 200)
    return FALSE;
  
  $place = $json->Placemark[0];
  
  list($lon, $lat, $level) = $place->Point->coordinates;
    
  return array(
    'address' => $place->address, 
    'lat' => (float) $lat, 
    'lng' => (float) $lon,
    'raw' => $place,
    );
}

