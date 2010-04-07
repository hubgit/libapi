<?php

class Connotea extends API {
  public $doc = 'http://www.connotea.org/webcite';
  public $def = 'CONNOTEA_AUTH';
  
  function bookmarks_for_item($uri, $data){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];

    $http = array('header' => sprintf('Authorization: Basic %s', base64_encode(Config::get('CONNOTEA_AUTH'))));
    $this->get_data('http://www.connotea.org/data/uri/' . md5($uri), array(), 'dom', $http);
    
    $this->xpath->registerNamespace('dcel', 'http://purl.org/dc/elements/1.1/');
    $this->xpath->registerNamespace('connotea', 'http://www.connotea.org/2005/01/schema#');
    
    foreach ($this->xpath('Post') as $node)
      $this->results[] = array(
        'user' => $this->xpath->query('dcel:creator', $node)->item(0)->nodeValue,
        'tags' => $this->xpath->query('dcel:subject', $node)->item(0)->nodeValue,
        'date' => $this->xpath->query('connotea:created', $node)->item(0)->nodeValue,
        'title' => $this->xpath->query('connotea:title', $node)->item(0)->nodeValue,
        'description' => $this->xpath->query('connotea:description', $node)->item(0)->nodeValue,
        );
        
    $this->total = count($this->results);
  }
  
  function metadata($data){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];

    $this->get_data('http://www.connotea.org/webcite', array(
      'uri' => $uri,
      'fmt' => 'json',
      ));
      
    return $this->data->citation;
  }
}