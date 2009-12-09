<?php

class Zemanta extends API {
  public $doc = 'http://developer.zemanta.com/';
  public $def = 'ZEMANTA';

  function entities($q, $query = NULL){
    if (!$text = $q['text'])
      return FALSE;
    
    $params = array(
      'text' => $text,
      'format' => 'json',
      'api_key' => Config::get('ZEMANTA'),
      'method' => 'zemanta.suggest_markup',
      'return_rdf_links' => 1,
    );
  
    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $json = $this->Config::get('YAHOO')get_data('http://api.zemanta.com/services/rest/0.0/', array(), 'json', $http);
  
    debug($json);
  
    if (!is_object($json) || $json->status != 'ok')
      return FALSE;
    
    $entities = array();
    $references = array();

    foreach ($json->markup->links as $item){
      $entities[] = array(
        'title' => $item->anchor,
        'score' => $item->confidence,
        'raw' => $item->target,
        );
    }
  
    return array($entities, $references);
  }
}