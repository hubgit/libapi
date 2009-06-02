<?php

# http://www.multimap.com/openapidocs/1.2/web_service/ws_geocoding.htm

return defined('MULTIMAP_API_KEY');

function geocode_multimap($q){
  $json = get_data('http://developer.multimap.com/API/geocode/1.2/' . urlencode(MULTIMAP_API_KEY), array(
    'qs' => $q,
    'output' => 'json',
  ));
  
  //debug($json);
    
  if (!is_object($json) || empty($json->result_set))
    return array(FALSE, array(FALSE, FALSE));
    
  $place = $json->result_set[0];
  
  return array($place->address->display_name, array((float) $place->point->lat, (float) $place->point->lon));
}

