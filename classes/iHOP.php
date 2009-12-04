<?php

class iHOP extends API {
  public $doc = 'http://www.ihop-net.org/UniPub/iHOP/webservices/';

  function entities_from_pmid($q){
    if (!$pmid = $q['pmid'])
      return FALSE;
     
    $xml = $this->get_data('http://ubio.bioinfo.cnio.es/biotools/iHOP/cgi-bin/getPubMed', array('pmid' => $pmid), 'xml');
  
    //debug($xml);
  
    if (!is_object($xml))
      return array();
  
    $xml->registerXPathNamespace('ihop', 'http://www.pdg.cnb.uam.es/UniPub/iHOP/xml');
  
    $sentences = $xml->xpath("ihop:iHOPsentence");
    if (empty($sentences))
      return FALSE; 
  
    $entities = array();
    $references = array();
  
    foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:MeSHLink") as $item)
      $entities['mesh'][(string) $item['meshId']] = (string) $item['term'];
  
    foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:chemicalCompound") as $item)
      $entities['chemical'][(string) $item['CID']] = (string) $item['name'];
  
    return array($entities, $references);
  }
}
