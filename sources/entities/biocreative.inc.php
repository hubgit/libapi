<?php

# http://bcms.bioinfo.cnio.es/

function entities_biocreative($q){ 
  if (!$pmid = $q['pmid'])
    return FALSE;
    
  $http = array('method'=> 'POST', 'content' => xmlrpc_encode_request('Annotations.getAnnotations', $pmid), 'header' => 'Content-Type: text/xml');
  $data = get_data('http://bcms.bioinfo.cnio.es/xmlrpc/', array(), 'raw', $http);
  
  $data = xmlrpc_decode($data);
     
  debug($data);
  
  if (!is_array($data) || isset($data['faultCode']))
    return array();
    
  $entities = array();
  $references = array();
  
  foreach ($data as $item){
    foreach ($item['mentions'] as $reference){
      $references[] = array(
        'name' => $reference['mention'],
        'start' => (int) $reference['offset'],
        'score' => (float) $reference['confidence'],
        );
    }
  }
  
  return array($entities, $references);
}
