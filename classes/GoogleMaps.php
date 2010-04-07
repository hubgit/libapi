<?php

class GoogleMaps extends API {
  public $doc = 'http://code.google.com/apis/maps/documentation/geocoding/index.html';
  public $def = 'GOOGLE_MAPS';
  
  function geocode($text){
    $this->get_data('http://maps.google.com/maps/geo', array(
      'q' => $text,
      'output' => 'json',
      'oe' => 'utf8',
      'sensor' => 'false',
      'key' => Config::get('GOOGLE_MAPS'),
    ));
    
    if ($this->data->Status->code != 200)
      return FALSE;
  
    $place = $this->data->Placemark[0];
  
    list($lon, $lat, $level) = $place->Point->coordinates;
    
    return array(
      'address' => $place->address, 
      'lat' => (float) $lat, 
      'lng' => (float) $lon,
      'raw' => $place,
      );
  }
  
  function search($text){

    $this->get_data('http://ajax.googleapis.com/ajax/services/search/local', array(
      'v' => '1.0',
      'key' => Config::get('GOOGLE_MAPS'),
      'rsz' => 'large',
      'q' => $text,
      )); 

    if ($this->data->responseStatus != 200)
      return FALSE;

    $this->results = $this->data->responseData->results;
    $this->total = $this->data->responseData->cursor->estimatedResultCount;
  }
}
