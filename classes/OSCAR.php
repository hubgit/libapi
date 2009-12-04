<?php

class OSCAR extends API {
  function entities_oscar($q){
    if (!$text = $q['text'])
      return FALSE;
    
    $params = array(
      'contents' => $text,
      'output' => 'markedup',
      );
    
    $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $xml = get_data('http://127.0.0.1:8181/oscar/Process', array(), 'xml', $http);
  
    if (!is_object($xml))
      return FALSE;
      
    //debug($xml);
  
    $dom = dom_import_simplexml($xml->BODY->DIV->P[0]);

    $entities = array();
    $references = array();
    
    // TODO: return standoff annotation?
    $position = 0;
    foreach ($dom->childNodes as $node){
      switch($node->nodeType){
        case XML_TEXT_NODE:
          $position += mb_strlen($node->nodeValue);
        break;

        case XML_ELEMENT_NODE:
          $start = $position;
          $end = $position += mb_strlen($node->nodeValue);
        
          $name = $node->getAttribute('surface');
          $type = $node->getAttribute('type');

          if (isset($entities[$type][$name]))
            $entities[$type][$name]['count']++;
          else
            $entities[$type][$name] = array(
              'count' => 1,
              'inchi' => $node->getAttribute('InChI'),
              'smiles' => $node->getAttribute('SMILES'),
              'identifiers' => explode(' ', $node->getAttribute('ontIDs')),
              );
        
          $references[] = array(
            'type' => $type,
            'uri' => $node->getAttribute('InChI'),
            'text' => $node->firstChild->nodeValue,
            'start' => $start,
            'end' => $end,
            'score' => $node->getAttribute('confidence'),
            'snippet' => snippet($text, $start, $end),
            );
        break; 
      }
    }

    //ksort($entities, SORT_STRING);
    return array($entities, $references);
  }
}
