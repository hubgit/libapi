<?php

class Connotea extends API {
  public $doc = 'http://www.connotea.org/webcite';
  public $def = 'CONNOTEA_AUTH';
  
  function bookmarks_for_item($q){
    if (!$q['uri'] && $q['doi'])
      $q['uri'] = 'http://dx.doi.org/' . $q['doi'];

    if (!$uri = $q['uri'])
      return FALSE;

    $auth = explode(':', Config::get('CONNOTEA_AUTH'));

    $dom =$this->get_data(
      sprintf('http://%s:%s@www.connotea.org/data/uri/%s', urlencode($auth[0]), urlencode($auth[1]), md5($uri)), 
      array(), 'dom');

    debug($dom);

    if (!is_object($dom))
      return FALSE;
    
    $items = array();
    foreach ($dom->getElementsByTagName('Post') as $node)
      $items[] = array(
        'user' => $node->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator')->item(0)->nodeValue,
        'tags' => $node->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'subject')->item(0)->nodeValue,
        'date' => $node->getElementsByTagNameNS('http://www.connotea.org/2005/01/schema#', 'created')->item(0)->nodeValue,
        'title' => $node->getElementsByTagNameNS('http://www.connotea.org/2005/01/schema#', 'title')->item(0)->nodeValue,
        'description' => $node->getElementsByTagNameNS('http://www.connotea.org/2005/01/schema#', 'description')->item(0)->nodeValue,
        );

    return array($items, array('total' => count($items));
  }
  
  function metadata($q){
    if (!$q['uri'] && $q['doi'])
      $q['uri'] = 'http://dx.doi.org/' . $q['doi'];

    if (!$uri = $q['uri'])
      return FALSE;

    $json =$this->get_data('http://www.connotea.org/webcite', array(
      'uri' => $uri,
      'fmt' => 'json',
      ));

    //debug($json);

    if (!is_object($json))
      return FALSE;

    return $json->citation;
  }
}