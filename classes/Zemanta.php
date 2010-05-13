<?php

class Zemanta extends API {
  public $doc = 'http://developer.zemanta.com/';
  public $def = 'ZEMANTA';
  
  private $server = 'http://api.zemanta.com/services/rest/0.0/';
  
  function extract_entities($text){    
    $params = array(
      'text' => $text,
      'format' => 'json',
      'api_key' => Config::get('ZEMANTA'),
      'method' => 'zemanta.suggest_markup',
      'return_rdf_links' => 1,
    );
  
    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data($this->server, array(), 'json', $http);
    
    if ($this->data->status != 'ok')
      throw new DataException('Response status not ok');
    
    foreach ($this->data->markup->links as $item)
      $this->entities[] = array(
        'title' => $item->anchor,
        'score' => $item->confidence,
        'raw' => $item->target,
        );
  }
  
  function suggest($text, $params = array()){
    $default = array(
      'text' => $text,
      'format' => 'json',
      'api_key' => Config::get('ZEMANTA'),
      'method' => 'zemanta.suggest',
    );
  
    $http = array('method' => 'POST', 'content' => http_build_query(array_merge($default, $params)), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data($this->server, array(), 'json', $http);
  }
  
  function similar($text, $n = 10){
    $this->suggest($text, array('articles_limit' => $n, 'return_images' => 0));
    $this->results = $this->data->articles;
  }
  
  function keywords($text){
    $this->suggest($text, array('return_images' => 0));
    $this->results = $this->data->keywords;
  }
}
