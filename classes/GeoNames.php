<?php

class GeoNames extends API {
  public $doc = 'http://www.geonames.org/export/geonames-search.html';

  function geocode($text){
    $json = $this->get_data('http://ws.geonames.org/searchJSON', array(
      'q' => $text,
      'maxRows' => 1,
      'lang' => 'en',
      //'style' => 'full',
    ));
  
    debug($json);
  
    if (!is_object($json) || empty($json->geonames))
      return FALSE;
    
    $place = $json->geonames[0];
  
    $name = $place->name;
    if (isset($place->adminName1) && $place->name != $place->adminName1)
      $name = implode(', ', array($name, $place->adminName1));
      
    return array(
      'address' => $name, 
      'lat' => (float) $place->lat, 
      'lng' => (float) $place->lng,
      'raw' => $place,
      );
  }
}