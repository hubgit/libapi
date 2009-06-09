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
  
  $xml->registerXPathNamespace('swrc', 'http://swrc.ontoware.org/ontology#');
  
  if (!is_object($xml))
    return FALSE;
  
  $data = array();
  foreach ($xml->xpath("swrc:Article/*") as $item)
    $data[$item->getName()] = (string) $item;
  
  $data['authors'] = xpath_items($xml, "swrc:Article/swrc:author/swrc:Person/swrc:name");    

  return $data;
}
