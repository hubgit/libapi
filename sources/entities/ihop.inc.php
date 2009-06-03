<?php

# http://www.ihop-net.org/UniPub/iHOP/webservices/

function entities_ihop($q){
  if (!$pmid = $q['pmid'])
    return FALSE;
     
  $xml = get_data('http://ubio.bioinfo.cnio.es/biotools/iHOP/cgi-bin/getPubMed', array('pmid' => $pmid), 'xml');
  
  //debug($xml);
  
  if (!is_object($xml))
    return array();
  
  $xml->registerXPathNamespace('ihop', 'http://www.pdg.cnb.uam.es/UniPub/iHOP/xml');  
  
  $entities = array();
  
  foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:MeSHLink") as $item)
    $entities['mesh'][(string) $item['meshId']] = array('title' => (string) $item['term']);
  
  foreach ($xml->xpath("ihop:iHOPsentence/ihop:iHOPatom/ihop:chemicalCompound") as $item)
    $entities['chemical'][(string) $item['CID']] = array('title' => (string) $item['name']);
  
  return array($entities);
}
