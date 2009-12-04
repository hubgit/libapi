<?php

class CrossRef extends API {
  public $doc = 'http://www.crossref.org/citedby.html';
  public $def = 'CROSSREF_AUTH';

  function citedby($q){
    if (!$doi = $q['doi'])
      return FALSE;

    $auth = explode(':', CROSSREF_AUTH);

    $xml = get_data('http://doi.crossref.org/servlet/getForwardLinks', array(
      'doi' => $doi,
      'usr' => $auth[0],
      'pwd' => $auth[1],
      ), 'xml');

    //debug($xml);

    if (!is_object($xml))
      return FALSE;

    $items = array();
    foreach ($xml->query_result->body->forward_link as $item){  
      $cite = $item->journal_cite;
      //debug($item);  
      $items[(string) $cite->doi] = array(
        'citedby' => (int) $cite['fl_count'],
        'year' => (int) $cite->year,
        'xml' => $cite,
        );
    }
    
    return array($items, array('total' => count($items));
  }

   
}