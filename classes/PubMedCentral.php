<?php

class PubMedCentral extends API {
  public $doc = 'http://www.pubmedcentral.nih.gov/utils/';
  
  function citedby($pmid){    
    $this->get_data('http://www.pubmedcentral.nih.gov/utils/entrez2pmcciting.cgi', array(
      'view' => 'xml',
      'id' => $pmid,
      ), 'xml');
    
    foreach ($this->data->REFORM->PMCID as $item)
      $this->results[] = (int) $item;
  }
  
  function pmc_to_entrez($ids){    
    $this->get_data('http://www.pubmedcentral.gov/utils/pmcentrez.cgi', array(
      'view' => 'xml',
      'id' => implode(',', $ids),
      ), 'xml');
    
    foreach ($this->data->REFORM as $item)
      $this->results[] = (int) $item->PMID;
  }
}
