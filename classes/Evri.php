<?php

class Evri extends API {
  public $doc = 'http://www.evri.com/developer/rest';
  
  function entities($q){
    if (!$text = $q['text'])
      return FALSE;
      
    $params = array(
      'uri' => 'http://www.example.com/',
      'text' => "title\n\n" . $text,
    );

    //$http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $xml = $this->get_data('http://api.evri.com/v1/media/entities.xml', $params, 'xml');

    debug($xml);

    if (!is_object($xml) || (string) $xml['status'] != 'OK')
      return FALSE;
    
    return $xml->graph->entities->entity;
  }  
}