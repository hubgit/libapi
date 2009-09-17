<?php

# http://www.ebi.ac.uk/webservices/whatizit/

function entities_whatizit($q){ 
  if (!$text = $q['text'])
    return FALSE;
    
  /* Proteins and Gene Ontology terms */
   
  $xml = whatizit_soap('whatizitSwissprotGo2', $text);
  if (!is_object($xml))
    return FALSE;
          
  $entities = array();
  $references = array();
  foreach ($xml->xpath('//ebi:uniprot') as $item){
    $entities['Protein'][(string) $item] = (string) $item['ids'];
  }

  foreach ($xml->xpath('//ebi:go') as $item){
    $id = (string) $item['concept'];
    $entities['GO'][$id] = (string) $item['term'];
    
    $references[] = array(
      'start' => (string) $item['id'],
      'text' => (string) $item,
      'score' => (string) $item['score'],
      'entity' => $id,
      );
  }
  
  /* Chemical compounds */
  
  /*$xml = whatizit_soap('whatizitOscar3', $text);
  if (!is_object($xml))
    return FALSE;

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
    
    $entities[$type][$id] = (string) $item['surface'];
        
    $references[] = array(
      'text' => (string) $item,
      'score' => (string) $item['weight'],
      'entity' => $id,
      );
  }*/
  
  $references = array();
      
  return array($entities, $references);
}

function whatizit_soap($pipeline, $text){  
  static $client;
  if (!is_object($client))
    //$client = new SoapClient('http://www.ebi.ac.uk/webservices/whatizit/ws?wsdl');
    $client = new SoapClient(dirname(__FILE__) . '/whatizit.wsdl');
  
  // hack for bug in XML response
  //$text = str_replace('<', '|', $text);
  
  $params = array(
    'text' => $text,
    'pipelineName' => $pipeline,
    'convertToHtml' => FALSE,
    );
    
  try{
    $result = $client->contact($params);
  } catch (SoapFault $exception) { debug($exception); exit(); return FALSE; }
  
  //debug($result);
  
  libxml_use_internal_errors(TRUE);
  
  libxml_clear_errors();
  $xml = simplexml_load_string($result->return);
  
  $errors = libxml_get_errors();
  foreach ($errors as $error)
    print display_xml_error($error, $xml);
  libxml_clear_errors();

  if (!empty($errors))
    return FALSE;
    
  $xml->registerXPathNamespace('ebi', 'http://www.ebi.ac.uk/z');
  
  return $xml;
}

// temporary - for debugging XML errors
function display_xml_error($error, $xml)
{
    $return  = $xml[$error->line - 1] . "\n";
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}