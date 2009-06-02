<?php

# http://www.geonames.org/export/geonames-search.html

return defined('GEONAMES_ENABLED');

function geocode_geonames($q){
  $json = get_data('http://ws.geonames.org/searchJSON', array(
    'q' => $q,
    'maxRows' => 1,
    'lang' => 'en',
    'style' => 'full',
  ));
  
  debug($json);
  
  if (!is_object($json) || empty($json->geonames))
    return array(FALSE, array(FALSE, FALSE));
    
  $place = $json->geonames[0];
  
  $name = $place->name;
  if (isset($place->adminName1) && $place->name != $place->adminName1)
    $name = implode(', ', array($name, $place->adminName1));
    
  return array($name, array($place->lat, $place->lng));
}

