<?php

class Belief extends API {
  public $doc = 'http://beliefnetworks.net/bnws/core.html';
  
  function concepts($text){      
    $params = array(
      'text' => $text,
      //'numresults' => 35,
    );

    //$http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://beliefnetworks.net/bnws/v1/recommendations/concepts', $params, 'xml');

    foreach ($this->data->recommendation as $item)
      $this->results[(string) $item] = (string) $item['weight'];
  }  
}