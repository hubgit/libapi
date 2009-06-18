<?php

# http://developer.yahoo.com/maps/rest/V1/geocode.html

return defined('YAHOO_KEY');

function geocode_yahoo($q){
  $xml = get_data('http://local.yahooapis.com/MapsService/V1/geocode', array(
    'location' => $q,
    'appid' => YAHOO_KEY,
  ), 'xml');
  
  //debug($xml);
  
  if (!is_object($xml))
    return FALSE;
    
  $xml->registerXPathNamespace('y', 'urn:yahoo:maps');
  
  $results = $xml->xpath("y:Result");
  if (empty($results))
    return FALSE;
  
  $place = $results[0]->children('urn:yahoo:maps');
  
  $name = array();
  foreach (array('Address', 'City', 'State', 'Zip', 'Country') as $field)
    if ($data = $place->{$field})
      $name[$field] = (string) $data;
  
  if (isset($name['State']) && isset($name['Zip'])){
    $name['State'] .= ' ' . $name['Zip'];
    unset($name['Zip']);
  }
    
  return array(
    'address' => implode(', ', $name), 
    'lat' => (float) $place->Latitude, 
    'lng' => (float) $place->Longitude,
    'raw' => $xml,
    );
}

