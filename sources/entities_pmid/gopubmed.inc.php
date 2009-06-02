<?php

function entities_pmid_gopubmed($pmid){  
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
    
    $entities[$type][$id] = array('title' => (string) $item['name']);
  }
  
  foreach ($xml->xpath("ygg:Documents/ygg:Document/ygg:Annotations/ygg:DocAnnotations[@type='Wiki']/ygg:Wiki") as $item){
    $entities['Wikipedia'][] = (string) $item['link'];
  }
  
  foreach ($xml->xpath("ygg:Documents/ygg:Document/ygg:Attr[@name='Proteins']/ygg:Value") as $item){
    $id = (string) $item['term']; 
    $data = (string) $item;
    $parts = explode(';', $data);
    $entities['Protein'][$id] = array(
      'uniprot' => explode('|', $parts[0]),
      'title' => $parts[1],
      );
  }
  
  return array($entities, $references);
}
