<?php

class Belief extends API {
  public $doc = 'http://beliefnetworks.net/bnws/core.html';
  
  function concepts($q){
    if (!$text = $q['text'])
      return FALSE;
      
    $params = array(
      'text' => $text,
      //'numresults' => 35,
    );

    //$http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $xml = $this->get_data('http://beliefnetworks.net/bnws/v1/recommendations/concepts', $params, 'xml');

    debug($xml);

    if (!is_object($xml))
      return FALSE;
      
    $concepts = array();
    foreach ($xml->recommendation as $item)
      $concepts[(string) $item] = (string) $item['weight'];
    
    return $concepts;
  }  
}