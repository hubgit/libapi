<?php

class OSCAR extends API {
  public $url = 'http://127.0.0.1:8181';
  
  public $annotations = array();
  
  function annotate($text){
    $params = array(
      'contents' => $text,
      'output' => 'markedup',
      );
    
    $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data($this->url . '/oscar/Process', array(), 'dom', $http);
    
    $root = $this->xpath->query('BODY/DIV/P[0]')->item(0);
      
    // TODO: return standoff annotation?
    $position = 0;
    foreach ($root->childNodes as $node){
      switch($node->nodeType){
        case XML_TEXT_NODE:
          $position += mb_strlen($node->nodeValue);
        break;

        case XML_ELEMENT_NODE:
          $start = $position;
          $end = $position += mb_strlen($node->nodeValue);
          
          $this->annotations[] = array(
            'type' => $node->getAttribute('type'),
            'start' => $start,
            'end' => $end,
            'text' => $node->firstChild->textContent,
            'score' => $node->getAttribute('confidence'),
            'data' => array(
              'inchi' => $node->getAttribute('InChI'),
              'smiles' => $node->getAttribute('SMILES'),
              'identifiers' => explode(' ', $node->getAttribute('ontIDs')),
              )
            );
        break; 
      }
    }
  }
}
