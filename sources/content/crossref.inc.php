<?php

# 

function content_crossref($q){
  if (!$doi = $q['doi'])
    return FALSE;

  $xml = get_data('http://www.crossref.org/openurl/', array(
    'noredirect' => 'true',
    'format' => 'unixref',
    'id' => 'info:doi/' . $doi,
    ), 'xml');
  
  //debug($xml);
  
  $xml->registerXPathNamespace('isi', 'http://www.isinet.com/xrpc41');
  
  if (!is_object($xml) || empty($xml->doi_record))
    return FALSE;
    
  $item = $record->crossref->journal;
  $article = $item->journal_article;
  $journal = $item->journal_metadata;
  $issue = $item->journal_issue;
  
  if (!is_object($article) || !is_object($issue))
    return FALSE;

  return $output;
}

