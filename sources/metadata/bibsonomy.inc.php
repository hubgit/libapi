<?php

# 

return (defined('BIBSONOMY_USER') && defined('BIBSONOMY_KEY'));

function metadata_bibsonomy($q){
  if (!$q['uri'] && $q['doi'])
    $q['uri'] = 'http://dx.doi.org/' . $q['doi'];
    
  if (!$uri = $q['uri'])
    return FALSE;
    
  $xml = get_data('http://scraper.bibsonomy.org/service', array(
    'url' => $uri,
    'format' => 'rdf+xml',
    ), 'xml');
  
  debug($xml);
  
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
