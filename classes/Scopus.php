<?php

class Scopus extends API {
  public $doc = 'http://searchapidocs.scopus.com';
  public $def = 'SCOPUS_KEY';

  function citedby($q){
    if (!$doi = $q['doi'])
      return FALSE;
    
    $data = $this->get_data('http://www.scopus.com/scsearchapi/search.url', array(
      'search' => sprintf('DOI("%s")', $doi),
      'callback' => 'test',
      'devId'=> SCOPUS_KEY,
      //'fields' => 'title,doctype,citedbycount,inwardurl,sourcetitle,issn,vol,issue,page,pubdate,eid,scp,doi,firstAuth,authlist,affiliations,abstract',
    ), 'raw');
  
    $json = json_decode(preg_replace('/^test\(/', '', preg_replace('/\)$/', '', $data)));
  
    //debug($json);
  
    if (!is_object($json) || !isset($json->PartOK)) // PartOK because developer key won't match referer header
      return FALSE;
  
    $result = $json->PartOK->Results[0];
  
    return array($result->inwardurl, array('total' => (int) $result->citedbycount));
  }
}

