<?php

class Bibsonomy extends API {
  public $doc = '';
  public $def = array('BIBSONOMY_USER', 'BIBSONOMY');
  
  function metadata($uri, $data = array()){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];
    
    $this->get_data('http://scraper.bibsonomy.org/service', array(
      'url' => $uri,
      'format' => 'rdf+xml',
      ), 'rdf');
      
    $this->xpath->registerNamespace('swrc', 'http://swrc.ontoware.org/ontology#');
  
    $item = array();
    foreach ($this->xpath->query("swrc:Article/*") as $node)
      $item[$node->nodeName] = $node->textContent;
  
    foreach ($this->xpath->query("swrc:Article/swrc:author/swrc:Person/swrc:name") as $node)
      $item['authors'][] = $node->textContent;
      
    $this->results[] = $item;
  }
}