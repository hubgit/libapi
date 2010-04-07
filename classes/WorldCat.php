<?php

class WorldCat extends API {
  public $doc = 'http://worldcat.org/devnet/wiki/BasicAPIDetails';
  public $def = 'WORLDCAT'; // https://worldcat.org/config/
    
  function search($q, $params = array()){
    $default = array(
      'q' => $q,
      'wskey' => Config::get('WORLDCAT'),
      'format' => 'atom', // atom|rss
      'start' => 1,
      'count' => 10,
      //'cformat' => 'apa', // apa, chicago, harvard, mla, turabian
    );

    // http://worldcat.org/webservices/catalog/search/opensearch?q=[query]&format=[atom|rss]&start=[start position]&count=[maximum number of records to return]&cformat=[citation format]&wskey=[your key]     
    $this->opensearch('http://www.worldcat.org/webservices/catalog/search/opensearch', array_merge($default, $params));
    
    foreach ($this->xpath->query('atom:entry') as $entry){      
      $item = array(
        'title' => $this->xpath->query('atom:title', $entry)->item(0)->textContent,
        'author' => $this->xpath->query('atom:author/atom:name', $entry)->item(0)->textContent,
        'link' => $this->xpath->query('atom:link/@href', $entry)->item(0)->textContent,
        'identifier' => array(),
        );
        
      foreach ($this->xpath->query('dc:identifier', $entry) as $identifier)
        $item['identifier'][] = $identifier->textContent;
        
      $this->results[] = $item;
    }  
  }  
}