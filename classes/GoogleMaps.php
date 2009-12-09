<?php

class GoogleMaps extends API {
  public $doc = 'http://code.google.com/apis/maps/documentation/geocoding/index.html';
  public $def = 'GOOGLE_MAPS';
  
  function geocode($q){
    $json = $this->get_data('http://maps.google.com/maps/geo', array(
      'q' => $q,
      'output' => 'json',
      'oe' => 'utf8',
      'sensor' => 'false',
      'key' => Config::get('GOOGLE_MAPS'),
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
}
