<?php

class WorldCat extends API {
  public $doc = 'http://worldcat.org/devnet/wiki/BasicAPIDetails';
  //public $def = 'WORLDCAT'; // https://worldcat.org/config/
    
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

   // http://worldcat.org/webservices/registry/search/Institutions?version=1.1&operation=searchRetrieve&recordSchema=info%3Arfa%2FrfaRegistry%2FschemaInfos%2FadminData&maximumRecords=100&startRecord=1&recordPacking=xml&query=local.country+%3D+%22FR%22
  function searchInstitution($q, $params = array()){
    $this->total = 0;    
    $this->items = array();

    $default = array(
      'query' => $q,
      'version' => '1.1',
      'operation' => 'searchRetrieve',
      'recordSchema' => 'info:rfa/rfaRegistry/schemaInfos/adminData',
      'maximumRecords' => 10,
      'startRecord' => 1,
      'recordPacking' => 'xml',
    );

    $this->get_data('http://worldcat.org/webservices/registry/search/Institutions', array_merge($default, $params), 'dom');

    $this->xpath->registerNamespace('srw', 'http://www.loc.gov/zing/srw/');
    $this->xpath->registerNamespace('a', 'info:rfa/rfaRegistry/xmlSchemas/adminData');

    $this->total = $this->xpath->query("srw:numberOfRecords")->item(0)->textContent;
    if (!$this->total)
      return false;
    
    foreach ($this->xpath->query("srw:records/srw:record/srw:recordData/a:adminData/a:resourceID") as $node)
      $this->items[] = str_replace('info:rfa/localhost/Institutions/', '', $node->textContent);
    
    return $this->items;
  }

  // http://worldcat.org/webservices/registry/enhancedContent/Institutions/94787
  function institutionDetails($id){
    $this->get_data('http://worldcat.org/webservices/registry/enhancedContent/Institutions/' . urlencode($id), array(), 'dom');
    
    $this->xpath->registerNamespace('i', 'info:rfa/rfaRegistry/xmlSchemas/institution');
    $this->xpath->registerNamespace('o', 'info:rfa/rfaRegistry/xmlSchemas/institutions/openURL');
    $this->xpath->registerNamespace('reg', 'http://worldcatlibraries.org/registry');
    $this->xpath->registerNamespace('res', 'http://worldcatlibraries.org/registry/resolver');
    
    $nodes = $this->xpath->query("o:openURL/reg:records/res:resolverRegistryEntry");
    if (!$nodes->length) return array('id' => $id);

    $node = $nodes->item(0);

    return array(
      //'id' => $id,
      'institutionID' => $this->xpath->query("res:InstitutionID", $node)->item(0)->textContent,
      'institutionName' => $this->xpath->query("res:institutionName", $node)->item(0)->textContent,
      'resolverID' => $this->xpath->query("res:resolver/res:resolverID", $node)->item(0)->textContent,
      'baseURL' => $this->xpath->query("res:resolver/res:baseURL", $node)->item(0)->textContent,
      'linkText' => $this->xpath->query("res:resolver/res:linkText", $node)->item(0)->textContent,
      'vendor' => $this->xpath->query("res:resolver/res:vendor", $node)->item(0)->textContent,
    );
  }
}
