<?php

class Postgenomic extends API {
  public $doc = 'http://www.postgenomic.com/wiki/doku.php?id=api';
  
  function citedby($args){
    $params = array(
      'format' => 'json',
      'type' => 'post',
      );
    
    if ($args['doi'])
      $params['citing_doi'] = $args['doi'];
    else if ($args['pmid'])
      $params['citing_paper'] = 'pmid:' . $args['pmid'];
    else if ($args['uri'])
      $params['citing_url'] = $args['uri'];
    else
      return FALSE;
    
    $this->get_data('http://www.postgenomic.com/api.php', $params);
    $this->total = count($this->data);
  }
}