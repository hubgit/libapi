<?php

function entities_gopubmed($q){ 
  if (!$text = $q['text'])
    return FALSE;
    
    
  static $client;
  if (!is_object($client))
    $client = new SoapClient('http://gopubmed4.biotec.tu-dresden.de/GoPubMedTermGenerationService/services/GoPubMedTermGeneration?wsdl', array('trace' => 1));
  
  $params = array(
    'texts' => array($text),
    'applicationCode' => 'test123',
    );
    
  //print_r($client->__getFunctions());
    
  try{
    $result = $client->generateConceptsFromText($params);
  } catch (SoapFault $exception) { print_r($exception); exit(); return FALSE; }
       
  $entities = $result->return;
  
  if (!is_array($entities))
    return array();
  
  $references = array();
  return array($entities, $references);
}

function entities_gopubmed_pmid($q){ 
  if (!$pmid = $q['pmid'])
    return FALSE;
     
  $xml = get_data('http://www.gopubmed.org/GoMeshPubMed/gomeshpubmed/Search/Xml', array('q' => $pmid . '[PMID]'), 'xml');
  
  //debug($xml);
  
  if (!is_object($xml))
    return array();
  
  $xml->registerXPathNamespace('ygg', 'http://yggdrasil.biotec.tu-dresden.de/3.0');  
  
  $entities = array();
  $references = array();
  
  foreach ($xml->xpath("ygg:Terms/ygg:Term") as $item){
    $id = (string) $item['id'];
    $type = (string) $item['localName'];
    
    $entities[$type][$id] = (string) $item['name'];
  }
  
  foreach ($xml->xpath("ygg:Documents/ygg:Document/ygg:Annotations/ygg:DocAnnotations[@type='Wiki']/ygg:Wiki") as $item){
    $entities['Wikipedia'][] = (string) $item['link'];
  }
  
  foreach ($xml->xpath("ygg:Documents/ygg:Document/ygg:Attr[@name='Proteins']/ygg:Value") as $item){
    $id = (string) $item['term']; 
    $data = (string) $item;
    $parts = explode(';', $data);
    $entities['Protein'][$id] = array(
      'uniprot' => $parts[0], //explode('|', $parts[0]),
      'title' => $parts[1],
      );
  }
  
  return array($entities, $references);
}
