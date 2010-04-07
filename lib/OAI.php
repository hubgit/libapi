<?php

class OAI extends API{  
  public $server;
  public $maxRecords;
  
  public $results = array();
    
  function __construct($server){
    $this->server = $server;
    $this->api = new API;
  }
  
  function query($verb, $path, $params = array()){
    do {
      $this->get($verb, $params);
      
      $nodes = $this->xpath->query('oai:' . $verb . $path);
      foreach ($nodes as $node)
        $this->results[] = $node;
      
      if ($this->maxRecords && (count($this->results) >= $this->maxRecords)){
        array_splice($this->results, $this->maxRecords);
        break;
      }
      
      $params = array('resumptionToken' => $this->xpath->query(sprintf('oai:%s/oai:resumptionToken', $verb))->item(0)->nodeValue);   
    } while ($params['resumptionToken']);
  }
  
  function get($verb, $params = array()){
    $params['verb'] = $verb;
    
    $this->get_data($this->server, $params, 'dom');
    
    $this->xpath->registerNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
    $this->xpath->registerNamespace('id', 'http://www.openarchives.org/OAI/2.0/oai-identifier');
    $this->xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3'); 
  }
  
  function getSampleIdentifier(){
    $this->query('Identify', '/oai:description/id:oai-identifier/id:sampleIdentifier');
    $this->sampleIdentifier = $this->results[0]->nodeValue;
  }
  
  function listSets(){
    $this->query('ListSets', '/oai:set/oai:setSpec');
    $this->sets = array();
    foreach ($this->results as $node)
      $this->sets[] = $node->nodeValue; 
  }
  
  function listMetadataFormats(){
    $this->query('ListMetadataFormats', '/oai:metadataFormat/oai:metadataPrefix', array('identifier' => $this->sampleIdentifier));
    $this->formats = array();
    foreach ($this->results as $node)
      $this->formats[] = $node->nodeValue;  
  }
  
  function listRecords($set, $format = 'oai_dc'){
    $this->query('ListRecords', '/oai:record/oai:metadata', array('set' => $set, 'metadataPrefix' => $format));
    $this->records = array();
    foreach ($this->results as $node)
      $this->records[] = $node; 
  } 
}
