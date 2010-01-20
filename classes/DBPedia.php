<?php

class DBPedia extends API {
  public $doc = 'http://lookup.dbpedia.org/api/search.asmx';
  
  function search($q){
    if (!$text = $q['text'])
      return FALSE;
    
    $class = $q['class'];
    $n = $q['n'] ? $q['n'] : 10;
      
    
    $client = new SOAPClient('http://lookup.dbpedia.org/api/search.asmx?WSDL');
    try {
      $result = $client->KeywordSearch(array(
        'QueryString' => $text,
        'QueryClass' => $class,
        'MaxHits' => $n,
        ));
    } catch (SOAPException $e){ debug($e); return FALSE; }
    
    //debug($result);
    
    if (!is_object($result) || empty($result->KeywordSearchResult->Result))
      return FALSE;
      
    $items = $result->KeywordSearchResult->Result;
    
    if (!is_array($items))
      $items = array($items);
    
    return array($items);
  }  
}