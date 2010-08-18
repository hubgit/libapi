<?php

class BioPortal extends API {
  public $doc = 'http://bioportal.bioontology.org/';
  
  // http://www.bioontology.org/wiki/index.php/Annotator_Web_service
  function annotate($text, $ontologies = array(), $params = array()){
    $this->annotations = array();

    if (!$text)
      return false;
      
    $default = array(
      'format' => 'xml',
      'filterNumber' => 'true',
      'isVirtualOntologyId' => 'false',
      'levelMax' => '0',
      'longestOnly' => 'true',
      'wholeWordOnly' => 'true',
      //'mappingTypes' => NULL,
      'minTermSize' => 3,
      'scored' => 'true',
      //'semanticTypes' => NULL,
      //'stopWords' => NULL,
      'withDefaultStopWords' => 'true',
      'isStopWordsCaseSensitive' => 'false',
      'withSynonyms' => 'true',
      'ontologiesToExpand' => implode(',', $ontologies),
      'ontologiesToKeepInResult' => implode(',', $ontologies),
      'textToAnnotate' => $text,
      );
      
    $params = array_merge($default, $params);
    
    $http = array('method' => 'POST', 'ignore_errors' => false, 'header' => 'Accept: */*;q=0.2', 'timeout' => 300); // 5 minute timeout
    $this->get_data('http://rest.bioontology.org/obs/annotator', $params, 'dom', $http);
    debug('response');
    debug($this->data);
    if (!is_object($this->data))
      return FALSE;
    
    file_put_contents(sys_get_temp_dir() . '/bioportal.xml', $this->data->saveXML());
    
    $nodes = $this->xpath->query('data/annotatorResultBean/annotations/annotationBean');
    if (!$nodes->length)
      return FALSE;
    
    foreach ($nodes as $node){
      $context = $this->xpath->query('context', $node)->item(0);
      $concept = $this->xpath->query('concept', $node)->item(0);
      
      $synonyms = array();
      foreach ($this->xpath->query('synonyms/string', $concept) as $synonym)
        $synonyms = $synonym->textContent;
      
      $conceptId = $this->xpath->query('localConceptId', $concept)->item(0)->nodeValue;
      list($ontology, $id) = explode('/', $conceptId);
            
      $this->annotations[] = array(
        'score' => $this->xpath->query('score', $node)->item(0)->nodeValue,
        'start' => $this->xpath->query('from', $context)->item(0)->nodeValue - 1,
        'end' => $this->xpath->query('to', $context)->item(0)->nodeValue,
        'text' => $this->xpath->query('term/name', $context)->item(0)->nodeValue,
        'title' => $this->xpath->query('preferredName', $concept)->item(0)->nodeValue,
        'uri' => $this->xpath->query('fullId', $concept)->item(0)->nodeValue,
        'ontology' => $ontology,
        'id' => $id,
        'type' => $this->xpath->query('semanticTypes[1]/semanticTypeBean/semanticType', $concept)->item(0)->nodeValue,
        );
    }
  } 
}