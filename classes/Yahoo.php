<?php

class Yahoo extends API {
  public $doc = '';
  //public $def = 'YAHOO';
  
  public $entities = array();
  
  # http://developer.yahoo.com/yql/
  function yql($query, $args = array(), $format = 'json'){
    if (!empty($args))
      $query = vsprintf($query, is_array($args) ? $args : array($args)); // FIXME: htmlspecialchars($arg, ENT_QUOTES, 'UTF-8'))

    $this->get_data('http://query.yahooapis.com/v1/public/yql', array(
      'q' => $query,
      'format' => $format,
      ), $format);
  }

  // http://developer.yahoo.com/maps/rest/V1/geocode.html
  function geocode($text){
    $this->get_data('http://local.yahooapis.com/MapsService/V1/geocode', array(
      'location' => $text,
      'appid' => Config::get('YAHOO'),
    ), 'dom');
      
    $this->xpath->registerNamespace('y', 'urn:yahoo:maps');
  
    $results = $this->data->getElementsByTagNameNS('urn:yahoo:maps', 'Result');
    if (empty($results))
      return FALSE;
  
    $place = $results->item(0);
  
    $name = array();
    foreach (array('Address', 'City', 'State', 'Zip', 'Country') as $field)
      if (($node = $place->getElementsByTagNameNS('urn:yahoo:maps', $field)) && $node->item(0)->nodeValue)
        $name[$field] = $node->item(0)->nodeValue;
  
    if (isset($name['State']) && isset($name['Zip'])){
      $name['State'] .= ' ' . $name['Zip'];
      unset($name['Zip']);
    }
    
    return array(
      'address' => implode(', ', $name), 
      'lat' => (float) $place->getElementsByTagName('Latitude')->item(0)->nodeValue, 
      'lng' => (float) $place->getElementsByTagName('Longitude')->item(0)->nodeValue,
      'raw' => $place,
      );
  }
  
  // http://developer.yahoo.com/geo/geoplanet/
  function geo_metadata($woeid){ 
    $suffix = isset($suffix) ? '/' . $suffix : '';

    $this->get_data('http://where.yahooapis.com/v1/place/' . $id . $suffix, array(
      'appid' => Config::get('YAHOO'),
      'format' => 'json',
      ), 'json');

    return isset($this->data->places) ? $this->data->places : $this->data->place;
  }
  
  function search($q, $params = array()){
    if (!$q)
      return FALSE;

    $default = array(
      'view' => 'language,delicious_toptags,delicious_saves,keyterms,searchmonkey_feed',
      'abstract' => 'long',
      'format' => 'json',
      'appid' => Config::get('YAHOO'),
    );

    $json = $this->get_data('http://boss.yahooapis.com/ysearch/web/v1/' . urlencode($q), array_merge($default, $params), 'json');
      
    $this->results = $json->ysearchresponse->resultset_web;
    $this->total = $json->ysearchresponse->totalhits;
  }
  
  function pagedata($q, $params = array()){    
    $default = array(
      'format' => 'json',
      'appid' => Config::get('YAHOO'),
    );
    
    $json = $this->get_data('http://boss.yahooapis.com/ysearch/se_pagedata/v1/' . urlencode($q), array_merge($default, $params));
    
    $this->results = $json->ysearchresponse->resultset_se_pagedata;
    $this->total = $json->ysearchresponse->totalhits;
  }
  
  # http://developer.yahoo.com/geo/placemaker/
  function placemaker($text){
    $params = array(
      'appid' => Config::get('YAHOO'),
      'documentType' => 'text/plain',
      'documentContent' => $text,
    );

    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://wherein.yahooapis.com/v1/document', array(), 'xml', $http);

    $this->data->registerXPathNamespace('y', 'http://wherein.yahooapis.com/v1/schema');

    $nodes = $this->data->xpath("y:document/y:placeDetails/y:place");
    foreach ($nodes as $item)
      $this->entities[(int) $item->woeId] = array(
        'type' => (string) $item->type,
        'title' => (string) $item->name,
        'lat' => (float) $item->centroid->latitude,
        'lng' => (float) $item->centroid->longitude,
        'score' => (int) $item->confidence,
        );

    $nodes = $this->data->xpath("y:document/y:referenceList/y:reference");
    foreach ($nodes as $item)
      $this->references[] = array(
        'start' => (int) $item->start, 
        'end' => (int) $item->end, 
        'text' => (string) $item->text, 
        'entity' => (string) $item->woeIds,
        'snippet' => snippet($text, (int) $item->start, (int) $item->end),
        );
  }
  
  function extract_entities($text){
    $params = array(
      'context' => $text,
      'query' => $context, // context for extraction (search terms)
      'output' => 'json',
      'appid' => Config::get('YAHOO'),
    );

    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction', array(), 'json', $http);

    $this->entities = $this->data->ResultSet->Result;
  } 
}