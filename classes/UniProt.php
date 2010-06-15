<?php

class UniProt extends API {
  public $doc = 'http://www.uniprot.org/';

  public $cache = TRUE;

  function item($id){
    $this->get_data(sprintf('http://www.uniprot.org/uniprot/%s.xml', rawurlencode($id)), array(), 'dom');
    $this->xpath->registerNamespace('u', 'http://uniprot.org/uniprot');
  }

  function search($term){
    $this->get_data('http://www.uniprot.org/uniprot/', array(
      'query' => $term,
      'sort' => 'score',
      'offset' => 0,
      'limit' => 10,
      'format' => 'xml',
      ), 'xml');

    $this->xpath->registerNamespace('u', 'http://uniprot.org/uniprot');
    $this->results = $this->xpath->query('u:entry');
  }

  function search_minimal($term){
      $this->opensearch('http://www.uniprot.org/uniprot/', array(
      'query' => $term,
      'sort' => 'score',
      'offset' => 0,
      'limit' => 10,
      'format' => 'rss',
      ));

    $this->results = array();
    foreach ($this->xpath->query('channel/item') as $item)
      $this->results[] = array(
       'link' => $this->xpath->query('link', $item)->item(0)->textContent,
       'title' => $this->xpath->query('title', $item)->item(0)->textContent,
       'description' => $this->xpath->query('description', $item)->item(0)->textContent,
       );
  }
}

