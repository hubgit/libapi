<?php

# http://www.ebi.ac.uk/webservices/whatizit/

function entities_whatizit($q){ 
  if (!$text = $q['text'])
    return FALSE;
    
  /* Proteins and Gene Ontology terms */
   
  $xml = whatizit_soap('whatizitSwissprotGo2', $text);
      
  $entities = array();
  $references = array();
  foreach ($xml->xpath('//ebi:uniprot') as $item){
    foreach (explode(',', (string) $item['ids']) as $id){
      $entities['Protein'][$id] = array(
        'title' => (string) $item,
      );
    }
  }

  foreach ($xml->xpath('//ebi:go') as $item){
    $id = (string) $item['concept'];
    $entities['GO'][$id] = array(
      'title' => (string) $item['term'],
    );
    
    $references[] = array(
      'start' => (string) $item['id'],
      'text' => (string) $item,
      'score' => (string) $item['score'],
      'entity' => $id,
      );
  }
  
  /* Chemical compounds */
  
  $xml = whatizit_soap('whatizitOscar3', $text);

  foreach ($xml->xpath('//ebi:e') as $item){
    $type = (string) $item['sem'];
    
    $id = NULL;
    foreach (array('InChI', 'ontIDs') as $attribute){
      if (isset($item[$attribute])){
        $id = (string) $item[$attribute];
        break;
      }
    }
    if (!$id)
      continue;
    
    $entities[$type][$id] = array(
      'title' => (string) $item['surface'],
    );
    
    $references[] = array(
      'text' => (string) $item,
      'score' => (string) $item['weight'],
      'entity' => $id,
      );
  }
      
  return array($entities, $references);
}

function whatizit_soap($pipeline, $text){  
  static $client;
  if (!is_object($client))
    //$client = new SoapClient('http://www.ebi.ac.uk/webservices/whatizit/ws?wsdl');
    $client = new SoapClient('../sources/entities/whatizit.wsdl');
  
  $params = array(
    'text' => $text,
    'pipelineName' => $pipeline,
    'convertToHtml' => FALSE,
    );
    
  try{
    $result = $client->contact($params);
  } catch (SoapFault $exception) { return FALSE; }
  
  //debug($result);
  
  $xml = simplexml_load_string($result->return);
  $xml->registerXPathNamespace('ebi', 'http://www.ebi.ac.uk/z');
  
  return $xml;
}