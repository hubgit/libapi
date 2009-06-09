<?php

# http://www.connotea.org/webcite

function metadata_connotea($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'http://dx.doi.org/' . $q['doi'];
    
  if (!$uri = $q['uri'])
    return FALSE;

  $json = get_data('http://www.connotea.org/webcite', array(
    'fmt' => 'json',
    'uri' => $uri,
    ));
  
  //debug($json);
  
  if (!is_object($json))
    return FALSE;
    
  return $json->citation;
}

