<?php

class BioCreative extends API {
  public $doc = 'http://bcms.bioinfo.cnio.es/';

  function entities($args){ 
    $this->validate($args, 'pmid'); extract($args);
    
    $http = array('method'=> 'POST', 'content' => xmlrpc_encode_request('Annotations.getAnnotations', $pmid), 'header' => 'Content-Type: text/xml');
    $data = $this->get_data('http://bcms.bioinfo.cnio.es/xmlrpc/', array(), 'raw', $http);
  
    $data = xmlrpc_decode($data);
     
    //debug($data);
  
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
}
