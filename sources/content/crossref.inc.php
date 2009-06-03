<?php

# http://www.crossref.org/requestaccount/

return defined('CROSSREF_AUTH');

function content_crossref($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'info:doi/' . $q['doi'];
    
  if (!$uri = $q['uri'])
    return FALSE;
    
  $xml = get_data('http://www.crossref.org/openurl/', array(
    'noredirect' => 'true',
    'format' => 'unixref',
    'id' => $uri,
    'pid' => CROSSREF_AUTH,
    ), 'xml');
  
  //debug($xml);
  
  if (!is_object($xml) || empty($xml->doi_record))
    return FALSE;
    
  $record = $xml->doi_record->crossref->journal;
  
  $article = $record->journal_article;
  $journal = $record->journal_metadata;
  $issue = $record->journal_issue;
  
  if (!is_object($article))
    return FALSE;

  return $record;
}

