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

    $this->results = array();
    foreach ($this->data->REFORM as $item)
      $this->results[] = (int) $item->PMID;
  }
  
  function get_uk_pmc_article($id){	
	$params = array(
		'verb' => 'GetRecord',
		'metadataPrefix' => 'pmc',
		'identifier' => 'oai:pubmedcentral.nih.gov:' . (int) $id,
	);
	
	$dom = $this->get_data('http://www.pubmedcentral.nih.gov/oai/oai.cgi', $params, 'dom');
	
	$this->xpath->registerNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
    $this->xpath->registerNamespace('id', 'http://www.openarchives.org/OAI/2.0/oai-identifier');
    $this->xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3'); 
	$this->xpath->registerNamespace('nlm', 'http://dtd.nlm.nih.gov/2.0/xsd/archivearticle');
	
	$article = $this->xpath->query('oai:GetRecord/oai:record/oai:metadata/nlm:article')->item(0);
	return $dom->saveXML($article);
  }
}

