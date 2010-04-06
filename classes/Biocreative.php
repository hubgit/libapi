<?php

class BioCreative extends API {
  public $doc = 'http://bcms.bioinfo.cnio.es/';
  
  public $references = array();

  function extract_entities($pmid){     
    $http = array('method'=> 'POST', 'content' => xmlrpc_encode_request('Annotations.getAnnotations', $pmid), 'header' => 'Content-Type: text/xml');
    $this->get_data('http://bcms.bioinfo.cnio.es/xmlrpc/', array(), 'xmlrpc', $http);
         
    if (isset($this->data['faultCode']))
      throw new DataException('Error code ' . $this->data['faultCode']);
      
    foreach ($this->data as $item)
      foreach ($item['mentions'] as $reference)
        $this->references[] = array(
          'name' => $reference['mention'],
          'start' => (int) $reference['offset'],
          'score' => (float) $reference['confidence'],
          );
  }
}
