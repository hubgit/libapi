<?php

class Alchemy extends API {
  public $doc = 'http://www.alchemyapi.com/api';
  public $def = 'ALCHEMY';
  
  public $entities;
    
  function extract_entities($text){      
    $params = array(
      'apikey' => Config::get('ALCHEMY'),
      'outputMode' => 'json',
      'text' => $text,
      //'disambiguate' => 1,
      //'linkedData' => 1,
      //'coreference' => 1,
    );

    $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://access.alchemyapi.com/calls/text/TextGetRankedNamedEntities', NULL, 'json', $http);

    if ($this->data->status != 'OK')
      return FALSE;

    $this->entities = $this->data->entities;
  }  
}