<?php

# http://www.pubmedcentral.nih.gov/utils/

function citedby_pubmedcentral($q){
  if (!$pmid = $q['pmid'])
    return FALSE;
    
  $xml = get_data('http://www.pubmedcentral.nih.gov/utils/entrez2pmcciting.cgi', array(
    'view' => 'xml',
    'id' => $pmid,
    ), 'xml');
    
  debug($xml);
  
  if (!is_object($xml))
    return FALSE;
    
  $items = array();
  foreach ($xml->REFORM->PMID as $item)
    $items[] = (int) $item;
    
  return array(count($items), $items);
}
