<?php

# http://searchapidocs.scopus.com/

return defined('SCOPUS_KEY');

function citedby_scopus($q){
  if (!$doi = $q['doi'])
    return FALSE;
    
  $data = get_data('http://www.scopus.com/scsearchapi/search.url', array(
    'search' => sprintf('DOI("%s")', $doi),
    'callback' => 'test',
    'devId'=> SCOPUS_KEY,
    //'fields' => 'title,doctype,citedbycount,inwardurl,sourcetitle,issn,vol,issue,page,pubdate,eid,scp,doi,firstAuth,authlist,affiliations,abstract',
  ), 'raw');
  
  $json = json_decode(preg_replace('/^test\(/', '', preg_replace('/\)$/', '', $data)));
  
  //debug($json);
  
  if (!is_object($json) || !isset($json->PartOK)) // PartOK because developer key doesn't match referer header
    return array(FALSE, array(FALSE, FALSE));
  
  $result = $json->PartOK->Results[0];
  
  return array((int) $result->citedbycount, $result->inwardurl);
}
