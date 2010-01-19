<?php

class Yahoo extends API {
  public $doc = '';
  public $def = 'YAHOO';
  
  # http://developer.yahoo.com/yql/
  function yql($query, $args = array(), $format = 'json'){
    if (!empty($args))
      $query = vsprintf($query, is_array($args) ? $args : array($args));

    return$this->get_data('http://query.yahooapis.com/v1/public/yql', array(
      'q' => $query,
      'format' => $format,
      ), $format);
  }

  // http://developer.yahoo.com/maps/rest/V1/geocode.html
  function geocode($text){
    $dom = $this->get_data('http://local.yahooapis.com/MapsService/V1/geocode', array(
      'location' => $text,
      'appid' => Config::get('YAHOO'),
    ), 'dom');
  
    //debug($dom->saveXML());
  
    if (!is_object($dom))
      return FALSE;
      
    $xpath = new DOMXPath($dom);    
    $xpath->registerNamespace('y', 'urn:yahoo:maps');
  
    $results = $dom->getElementsByTagNameNS('urn:yahoo:maps', 'Result');
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
  function geo_metadata($q){ 
    if (!$id = $q['woeid'])
     return FALSE;

    $suffix = isset($q['suffix']) ? '/' . $q['suffix'] : '';

    $json = $this->get_data('http://where.yahooapis.com/v1/place/' . $id . $suffix, array(
      'appid' => Config::get('YAHOO'),
      'format' => 'json',
      ));

    //debug($json);

    if (!is_object($json))
      return FALSE;

    return isset($json->places) ? $json->places : $json->place;
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

    $json = $this->get_data('http://boss.yahooapis.com/ysearch/web/v1/' . urlencode($q), array_merge($default, $params));

    //debug($json);

    if (!is_object($json))
      return FALSE;

    return array($json->ysearchresponse->resultset_web, array('total' => (int) $json->ysearchresponse->totalhits));
  }
  
  # http://developer.yahoo.com/geo/placemaker/
  function placemaker($q){
    if (!$text = $q['text'])
      return FALSE;

    $params = array(
      'appid' => Config::get('YAHOO'),
      'documentType' => 'text/plain',
      'documentContent' => $text,
    );

    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $xml = $this->get_data('http://wherein.yahooapis.com/v1/document', array(), 'xml', $http);

    //debug($xml);

    if (!is_object($xml))
      return array();

    $xml->registerXPathNamespace('y', 'http://wherein.yahooapis.com/v1/schema');

    $entities = array();
    $nodes = $xml->xpath("y:document/y:placeDetails/y:place");
    if (!empty($nodes)){
      foreach ($nodes as $item){
        $id = (int) $item->woeId;
        $type = (string) $item->type;
        $entities[$type][$id] = array(
          'title' => (string) $item->name,
          'lat' => (float) $item->centroid->latitude,
          'lng' => (float) $item->centroid->longitude,
          'score' => (int) $item->confidence,
          );
      }
    }

    $references = array();
    $nodes = $xml->xpath("y:document/y:referenceList/y:reference");
    if (!empty($nodes)){
      foreach ($nodes as $item){
        $id = (string) $item->woeIds;
        $references[] = array(
          'start' => (int) $item->start, 
          'end' => (int) $item->end, 
          'text' => (string) $item->text, 
          'entity' => $id,
          'snippet' => snippet($text, (int) $item->start, (int) $item->end),
          );
      }
    }

    return array($entities, $references);
  }
  
  function entities($q){
    if (!$text = $q['text'])
      return FALSE;

    $params = array(
      'context' => $text,
      'query' => $q['context'], // context for extraction (search terms)
      'output' => 'json',
      'appid' => Config::get('YAHOO'),
    );

    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $json = $this->get_data('http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction', array(), 'json', $http);

    //debug($json);

    if (!is_object($json))
      return array();

    return array($json->ResultSet->Result);
  }
  
}