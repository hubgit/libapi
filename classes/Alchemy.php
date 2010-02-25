<?php

class Alchemy extends API {
  public $doc = 'http://www.alchemyapi.com/api';
  public $def = 'ALCHEMY';
  
  function entities($args){
    $this->validate($args, 'text'); extract($args);
      
    $params = array(
      'apikey' => Config::get('ALCHEMY'),
      'outputMode' => 'json',
      'text' => $text,
      //'disambiguate' => 1,
      //'linkedData' => 1,
      //'coreference' => 1,
    );

    $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $json = $this->get_data('http://access.alchemyapi.com/calls/text/TextGetRankedNamedEntities', NULL, 'json', $http);

debug($json);

    if (!is_object($json) || $json->status != 'OK')
      return FALSE;
      
    return array($json->entities);
  }  
}