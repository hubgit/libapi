<?php

# http://www.crossref.org/requestaccount/

return defined('CROSSREF_AUTH');

function metadata_crossref($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'info:doi/' . $q['doi'];
    
  if (!($uri = $q['uri']) && empty($q['openurl']))
    return FALSE;

  $params = array(
    'noredirect' => 'true',
    'format' => 'unixref',
    'pid' => CROSSREF_AUTH,
    );
    
  if ($uri)
    $params['id'] = $uri;
  
  if (!empty($q['openurl']))
    $params = array_merge($params, $q['openurl']);
    
  $xml = get_data('http://www.crossref.org/openurl/', $params, 'xml');
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

