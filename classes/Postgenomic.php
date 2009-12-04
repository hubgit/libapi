<?php

class Postgenomic extends API {
  public $doc = 'http://www.postgenomic.com/wiki/doku.php?id=api';
  
  function citedby($q){
    $params = array(
      'format' => 'json',
      'type' => 'post',
      );
    
    if ($q['doi'])
      $params['citing_doi'] = $q['doi'];
    else if ($q['pmid'])
      $params['citing_paper'] = 'pmid:' . $q['pmid'];
    else if ($q['uri'])
      $params['citing_url'] = $q['uri'];
    else
      return FALSE;
    
    $json = get_data('http://www.postgenomic.com/api.php', $params);
    
    //debug($json);
  
    if (!is_array($json))
      return FALSE;
    
    return array($json, array('total' => count($json)));
  }
}