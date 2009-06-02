<?php

# http://developer.yahoo.com/maps/rest/V1/geocode.html

return defined('YAHOO_API_KEY');

function geocode_yahoo($q){
  $xml = get_data('http://local.yahooapis.com/MapsService/V1/geocode', array(
    'location' => $q,
    'appid' => YAHOO_API_KEY,
  ), 'xml');
  
  //debug($xml);
  
  $xml->registerXPathNamespace('y', 'urn:yahoo:maps');
  
  $results = $xml->xpath("//y:Result");
  
  if (!is_object($results) || empty($results))
    return array(FALSE, array(FALSE, FALSE));
    
  $place = $results[0]->children('urn:yahoo:maps');
  
  $name = array();
  foreach (array('Address', 'City', 'State', 'Zip', 'Country') as $field)
    if ($data = $place->{$field})
      $name[$field] = (string) $data;
  
  if (isset($name['State']) && isset($name['Zip'])){
    $name['State'] .= ' ' . $name['Zip'];
    unset($name['Zip']);
  }
    
  return array(implode(', ', $name), array((string) $place->Latitude, (string) $place->Longitude));
}

