<?php

class iHOP extends API {
  public $doc = 'http://www.ihop-net.org/UniPub/iHOP/webservices/';
  
  public $entities = array();

  function entities_from_pmid($pmid){
    $this->get_data('http://ubio.bioinfo.cnio.es/biotools/iHOP/cgi-bin/getPubMed', array('pmid' => $pmid), 'xml');  
    $xml->registerXPathNamespace('ihop', 'http://www.pdg.cnb.uam.es/UniPub/iHOP/xml');
  
    $sentences = $this->data->xpath("ihop:iHOPsentence");
    if (empty($sentences))
      return FALSE; 

    foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:MeSHLink") as $item)
      $this->entities['mesh'][(string) $item['meshId']] = (string) $item['term'];
  
    foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:chemicalCompound") as $item)
      $this->entities['chemical'][(string) $item['CID']] = (string) $item['name'];
  }
}
