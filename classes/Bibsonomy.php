<?php

class Bibsonomy extends API {
  public $doc = '';
  public $def = array('BIBSONOMY_USER', 'BIBSONOMY');

  function metadata($args){
    if (!$args['uri'] && $args['doi'])
      $args['uri'] = 'http://dx.doi.org/' . $args['doi'];
    
    $this->validate($args, 'uri'); extract($args);

    $xml = $this->get_data('http://scraper.bibsonomy.org/service', array(
      'url' => $uri,
      'format' => 'rdf+xml',
      ), 'rdf');
  
    //debug($xml);
  
    $xml->registerXPathNamespace('swrc', 'http://swrc.ontoware.org/ontology#');
  
    if (!is_object($xml))
      return FALSE;
  
    $data = array('raw' => $xml);
    foreach ($xml->xpath("swrc:Article/*") as $item)
      $data[$item->getName()] = (string) $item;
  
    $data['authors'] = $this->xpath_items($xml, "swrc:Article/swrc:author/swrc:Person/swrc:name");    

    return $data;
  }
}