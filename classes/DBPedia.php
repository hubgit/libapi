<?php

class DBPedia extends API {
  public $doc = 'http://lookup.dbpedia.org/api/search.asmx';
  
  function search($args){
    $this->validate($args, 'text', array('n' => 10, 'class' => 'x')); extract($args);      

    $params = array(
      'QueryString' => $text,
      'QueryClass' => $class,
      'MaxHits' => $n,
    );
    
    $dom = $this->get_data('http://lookup.dbpedia.org/api/search.asmx/KeywordSearch', $params, 'dom'); 
    //debug($dom->saveXML());   
    
    if (!is_object($dom))
      return FALSE;
      
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('db', 'http://lookup.dbpedia.org/');
    return $xpath->query('db:Result');
  }  
}