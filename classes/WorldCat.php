<?php

class WorldCat extends API {
  public $doc = 'http://worldcat.org/devnet/wiki/BasicAPIDetails';
  public $def = 'WORLDCAT'; // https://worldcat.org/config/
  
  function search($q, $params = array()){
    if (!$q)
      return FALSE;
    $default = array(
      'q' => $q,
      'wskey' => Config::get('WORLDCAT'),
      'format' => 'atom', // atom|rss
      'start' => 1,
      'count' => 10,
      //'cformat' => 'apa', // apa, chicago, harvard, mla, turabian
    );

    // http://worldcat.org/webservices/catalog/search/opensearch?q=[query]&format=[atom|rss]&start=[start position]&count=[maximum number of records to return]&cformat=[citation format]&wskey=[your key]     
    list($xml, $meta) = $this->opensearch('http://www.worldcat.org/webservices/catalog/search/opensearch', array_merge($default, $params));
    
    $items = array();
    foreach ($xml->xpath('atom:entry') as $entry){
      $entry->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
      
      $item = array(
        'title' => (string) current($entry->xpath('atom:title')),
        'author' => (string) current($entry->xpath('atom:author/atom:name')),
        'link' => (string) current($entry->xpath('atom:link/@href')),
        'identifier' => array(),
        );
        
      foreach ($entry->xpath('dc:identifier') as $identifier)
        $item['identifier'][] = (string) $identifier;
        
      $items[] = $item;
    }
    
    return array($items, $meta);
  }  
}