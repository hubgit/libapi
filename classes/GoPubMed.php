<?php

class GoPubMed extends API {
  function annotate($text){ 
    $params = array(
      'texts' => array($text),
      'applicationCode' => 'test123',
      );
    
    $this->soap('http://gopubmed4.biotec.tu-dresden.de/GoPubMedTermGenerationService/services/GoPubMedTermGeneration?wsdl', 'generateConceptsFromText', $params);
        
    if (is_array($this->data->return))
      $this->entities = $this->data->return;
  }

  function entities_from_pmid($pmid){      
    $this->get_data('http://www.gopubmed.org/GoMeshPubMed/gomeshpubmed/Search/Xml', array('q' => $pmid . '[PMID]'), 'dom');
    $this->xpath->registerNamespace('ygg', 'http://yggdrasil.biotec.tu-dresden.de/3.0');  
  
    foreach ($this->xpath->query("ygg:Terms/ygg:Term") as $node){
      $id = $node->getAttribute('id')->item(0)->nodeValue;
      $type = $node->getAttribute('localName')->item(0)->nodeValue;
    
      $this->entities[$type][$id] = $node->getAttribute('name')->item(0)->nodeValue;
    }
  
    foreach ($this->xpath->query("ygg:Documents/ygg:Document/ygg:Annotations/ygg:DocAnnotations[@type='Wiki']/ygg:Wiki") as $node)
      $this->entities['Wikipedia'][] = $node->getAttribute('link')->item(0)->nodeValue;
  
    foreach ($this->xpath->query("ygg:Documents/ygg:Document/ygg:Attr[@name='Proteins']/ygg:Value") as $node){
      $id = $node->getAttribute('term')->item(0)->nodeValue; 
      $data = $node->textContent; 
      $parts = explode(';', $data);
      $this->entities['Protein'][$id] = array(
        'uniprot' => $parts[0], //explode('|', $parts[0]),
        'title' => $parts[1],
        );
    }
  }
}
