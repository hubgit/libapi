<?php

class ProteinOntology extends BioPortal{
  public $doc = 'http://purl.bioontology.org/ontology/PRO';
  public $cache = TRUE;
  
  function search($args, $params = array()){
    unset($this->total, $this->data);
    
    $this->results = array();
  }
  
  function annotate($text){
    parent::annotate($text, array(42919)); // 42919 = version-specific localOntologyId from http://rest.bioontology.org/obs/ontologies
    foreach ($this->annotations as &$annotation)
      $annotation['type'] = 'protein';
  }
}
