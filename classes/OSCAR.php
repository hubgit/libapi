<?php

class OSCAR extends API {
  public $url = 'http://localhost:8181';
  public $cache = TRUE;
    
  function annotate($text){
    $params = array(
      'contents' => $text,
      'output' => 'markedup',
      );
    
    $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data($this->url . '/Process', array(), 'dom', $http);
    
    if (!is_object($this->data))
      return false;
      
    file_put_contents(sys_get_temp_dir() . '/oscar.txt', $text);
    file_put_contents(sys_get_temp_dir() . '/oscar.xml', $this->data->saveXML());
      
    $this->annotations = array();
    $position = 0;
    
    foreach ($this->xpath->query('BODY/DIV/P') as $root){
      foreach ($root->childNodes as $node){
        switch ($node->nodeType){
          case XML_TEXT_NODE:
            $position += mb_strlen($node->nodeValue);
          break;

          case XML_ELEMENT_NODE:
            $start = $position;
            $end = $position += mb_strlen($node->nodeValue);
            
            $id = $node->getAttribute('id');
            
            $properties = array(
              'iupac:inchi' => $node->getAttribute('InChI'),
              'chem:smiles' => $node->getAttribute('SMILES'),
              );
              
            $identifiers = explode(' ', $node->getAttribute('ontIDs'));
            foreach ($identifiers as $identifier)
              if (strpos($identifier, 'CHEBI:') === 0)
                $properties['chebi:id'] = $identifier;
          
            $this->annotations[] = array(
              'type' => $node->getAttribute('type'),
              'start' => $start,
              'end' => $end,
              'text' => $node->getAttribute('surface'),
              'score' => $node->getAttribute('confidence'),
              'data' => $properties
              );
          break; 
        }
      }
      $position += 2;
    }
    debug($this->annotations);
    return $this->annotations;
  }
}
