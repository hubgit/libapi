<?php

class GeoSpeciesOntology extends BioPortal{
  public $doc = ' 	 http://purl.bioontology.org/ontology/geospecies';
  public $cache = TRUE;
  
  function search($args, $params = array()){
    unset($this->total, $this->data);
    $this->results = array();
  }
  
  function annotate($text){
    parent::annotate($text, array(39933)); // 39933 = version-specific localOntologyId from http://rest.bioontology.org/obs/ontologies 1247 = virtualOntologyId
    foreach ($this->annotations as &$annotation)
      $annotation['type'] = 'species';
  }
}
