<?php

class GoogleMaps extends API {
  public $doc = 'http://code.google.com/apis/maps/documentation/geocoding/index.html';
  public $def = 'GOOGLE_MAPS';
  
  function geocode($text){
    $json = $this->get_data('http://maps.google.com/maps/geo', array(
      'q' => $text,
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
  
  function search($q){
    if (!$text = $q['text'])
      return false;
      
     $json = $this->get_data('http://ajax.googleapis.com/ajax/services/search/local', array(
        'v' => '1.0',
        'key' => Config::get('GOOGLE_MAPS'),
        'rsz' => 'large',
        'q' => $text,
     )); 
     
    debug($json);

    if (!is_object($json) || $json->responseStatus != 200)
       return FALSE;
        
    return array($json->responseData->results, array('total' => $json->responseData->cursor->estimatedResultCount));
  }
}
