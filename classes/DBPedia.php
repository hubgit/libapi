<?php

class DBPedia extends API {
  public $doc = 'http://lookup.dbpedia.org/api/search.asmx';
    
  function search($text, $n = 10, $class = 'x'){
    $params = array(
      'QueryString' => $text,
      'QueryClass' => $class,
      'MaxHits' => $n,
    );
    
    $this->get_data('http://lookup.dbpedia.org/api/search.asmx/KeywordSearch', $params, 'dom');
    $this->xpath->registerNamespace('db', 'http://lookup.dbpedia.org/');
    $this->results = $this->xpath->query('db:Result');
  }  
}