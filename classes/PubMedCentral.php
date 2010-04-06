<?php

class PubMedCentral extends API {
  public $doc = 'http://www.pubmedcentral.nih.gov/utils/';
  
  public $results = array();

  function citedby($pmid){    
    $this->get_data('http://www.pubmedcentral.nih.gov/utils/entrez2pmcciting.cgi', array(
      'view' => 'xml',
      'id' => $pmid,
      ), 'xml');
    
    foreach ($xml->REFORM->PMCID as $item)
      $this->results[] = (int) $item;
  }
  
  function pmc_to_entrez($ids){    
    $this->get_data('http://www.pubmedcentral.gov/utils/pmcentrez.cgi', array(
      'view' => 'xml',
      'id' => implode(',', $ids),
      ), 'xml');
    
    foreach ($xml->REFORM as $item)
      $this->results[] = (int) $item->PMID;
  }
}
