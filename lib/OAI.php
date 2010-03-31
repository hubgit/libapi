<?php

class OAI extends API{  
  public $server;
  public $maxRecords;
  
  function __construct($server){
    $this->server = $server;
    $this->api = new API;
  }
  
  function query($verb, $path, $params = array()){
    $items = array();
    $token = new stdClass;
    do {
      if ($token->length){
        foreach ($params as $key => $value)
          if ($key != 'verb')
            unset($params[$key]);
        $params['resumptionToken'] = $token->item(0)->nodeValue;
      }

      $xpath = $this->get($verb, $params);
      
      $nodes = $xpath->query('oai:' . $verb . $path);
      if (!empty($nodes))
        foreach ($nodes as $node)
          $items[] = $node;
      
      if ($this->maxRecords && (count($items) >= $this->maxRecords)){
        array_splice($items, $this->maxRecords);
        break;
      }
      
      $token = $xpath->query(sprintf('oai:%s/oai:resumptionToken', $verb));
    } while ($token->length);

    return $items;
  }
  
  function get($verb, $params = array()){
    $params['verb'] = $verb;
    
    $dom = $this->get_data($this->server, $params, 'dom');
    debug($dom->saveXML());
    
    $xpath = new DOMXpath($dom);
    $xpath->registerNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
    $xpath->registerNamespace('id', 'http://www.openarchives.org/OAI/2.0/oai-identifier');
    $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3'); 

    return $xpath;
  }
  
  function getSampleIdentifier(){
    $nodes = $this->query('Identify', '/oai:description/id:oai-identifier/id:sampleIdentifier');
    $this->sampleIdentifier = $nodes[0]->nodeValue;
  }
  
  function listSets(){
    $nodes = $this->query('ListSets', '/oai:set/oai:setSpec');
    $this->sets = array();
    foreach ($nodes as $node)
      $this->sets[] = $node->nodeValue; 
  }
  
  function listMetadataFormats(){
    $nodes = $this->query('ListMetadataFormats', '/oai:metadataFormat/oai:metadataPrefix', array('identifier' => $this->sampleIdentifier));
    $this->formats = array();
    foreach ($nodes as $node)
      $this->formats[] = $node->nodeValue;  
  }
  
  function listRecords($set, $format = 'oai_dc'){
    $nodes = $this->query('ListRecords', '/oai:record/oai:metadata', array('set' => $set, 'metadataPrefix' => $format));
    $this->records = array();
    foreach ($nodes as $node)
      $this->records[] = $node; 
  } 
}
