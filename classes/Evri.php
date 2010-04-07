<?php

class Evri extends API {
  public $doc = 'http://www.evri.com/developer/rest';
    
  function annotate($text){      
    $params = array(
      'uri' => 'http://www.example.com/',
      'text' => "title\n\n" . $text,
    );

    //$http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://api.evri.com/v1/media/entities.json', $params, 'json');

    if ($this->data->evriThing->{'@status'} != 'OK')
      return FALSE;
    
    $this->entities = $this->data->evriThing->graph->entities->entity;
  }  
}