<?php

class Scopus extends API {
  public $doc = 'http://searchapidocs.scopus.com';
  public $def = 'SCOPUS_KEY';

  public $results = array();
  public $total;
  
  function citedby($doi){    
    $this->get_data('http://www.scopus.com/scsearchapi/search.url', array(
      'search' => sprintf('DOI("%s")', $doi),
      'callback' => 'test',
      'devId'=> SCOPUS_KEY,
      //'fields' => 'title,doctype,citedbycount,inwardurl,sourcetitle,issn,vol,issue,page,pubdate,eid,scp,doi,firstAuth,authlist,affiliations,abstract',
    ), 'raw');
  
    $this->data = json_decode(preg_replace('/^test\(/', '', preg_replace('/\)$/', '', $this->data)));
  
    if (!isset($json->PartOK)) // PartOK because developer key won't match referer header
      return FALSE;
  
    $result = $json->PartOK->Results[0];
    
    $this->results[] = $result->inwardurl;
    $this->total = $result->citedbycount;
  }
}

