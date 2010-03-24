<?php

class PubMedCentral extends API {
  public $doc = 'http://www.pubmedcentral.nih.gov/utils/';

  function citedby($args){
    $this->validate($args, 'pmid'); extract($args);
    
    $xml = $this->get_data('http://www.pubmedcentral.nih.gov/utils/entrez2pmcciting.cgi', array(
      'view' => 'xml',
      'id' => $pmid,
      ), 'xml');
    
    //debug($xml);
  
    if (!is_object($xml))
      return FALSE;
    
    $items = array();
    foreach ($xml->REFORM->PMCID as $item)
      $items[] = (int) $item;
    
    return array($items, array('total' => count($items)));
  }
  
  function pmc_to_entrez($args){
    $this->validate($args, 'ids'); extract($args);
    
    $xml = $this->get_data('http://www.pubmedcentral.gov/utils/pmcentrez.cgi', array(
      'view' => 'xml',
      'id' => implode(',', $ids),
      ), 'xml');
    
    //debug($xml);
  
    if (!is_object($xml))
      return FALSE;
    
    $items = array();
    foreach ($xml->REFORM as $item)
      $items[] = (int) $item->PMID;
    
    return $items;
  }
}
