<?php

class MultiMap extends API {
  public $doc = 'http://www.multimap.com/openapidocs/1.2/web_service/ws_geocoding.htm';
  public $def = 'MULTIMAP_KEY';

  function geocode($q){
    $json = get_data('http://developer.multimap.com/API/geocode/1.2/' . urlencode(MULTIMAP_KEY), array(
      'qs' => $q,
      'output' => 'json',
    ));
  
    debug($json);
    
    if (!is_object($json) || empty($json->result_set))
      return FALSE;
    
    $place = $json->result_set[0];
  
    return array(
      'address' => $place->address->display_name, 
      'lat' => (float) $place->point->lat, 
      'lng' => (float) $place->point->lon,
      'raw' => $place,
      );
  }
}
