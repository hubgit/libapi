<?php

class Yahoo extends API {
  public $doc = '';
  //public $def = 'YAHOO';
  
  # http://developer.yahoo.com/yql/
  function yql($query, $args = array(), $format = 'json'){
    if (!empty($args))
      $query = vsprintf($query, is_array($args) ? $args : array($args)); // FIXME: htmlspecialchars($arg, ENT_QUOTES, 'UTF-8'))

    $this->get_data('http://query.yahooapis.com/v1/public/yql', array(
      'q' => $query,
      'format' => $format,
      ), $format);
      
    return $this->data;
  }

  // http://developer.yahoo.com/maps/rest/V1/geocode.html
  function geocode($text){
    $this->get_data('http://local.yahooapis.com/MapsService/V1/geocode', array(
      'location' => $text,
      'appid' => Config::get('YAHOO'),
    ), 'dom');
      
    $this->xpath->registerNamespace('y', 'urn:yahoo:maps');
  
    $results = $this->xpath->query('y:Result');
    if (empty($results))
      return FALSE;
  
    $place = $results->item(0);
  
    $name = array();
    foreach (array('Address', 'City', 'State', 'Zip', 'Country') as $field){
      $nodes = $this->xpath->query('y:' . $field, $place);
      if ($nodes->length)
        $name[$field] = $nodes->item(0)->nodeValue;
    }
  
    if (isset($name['State']) && isset($name['Zip'])){
      $name['State'] .= ' ' . $name['Zip'];
      unset($name['Zip']);
    }
    
    return array(
      'address' => implode(', ', $name), 
      'lat' => (float) $this->xpath->query('Latitude', $place)->item(0)->nodeValue, 
      'lng' => (float) $this->xpath->query('Longitude', $place)->item(0)->nodeValue,
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

    $this->get_data('http://boss.yahooapis.com/ysearch/web/v1/' . urlencode($q), array_merge($default, $params), 'json');
      
    $this->results = $this->data->ysearchresponse->resultset_web;
    $this->total = $this->data->ysearchresponse->totalhits;
  }
  
  function pagedata($q, $params = array()){    
    $default = array(
      'format' => 'json',
      'appid' => Config::get('YAHOO'),
    );
    
    $this->get_data('http://boss.yahooapis.com/ysearch/se_pagedata/v1/' . urlencode($q), array_merge($default, $params));
    
    $this->results = $this->data->ysearchresponse->resultset_se_pagedata;
    $this->total = $this->data->ysearchresponse->totalhits;
  }
  
  # http://developer.yahoo.com/geo/placemaker/
  function placemaker($text){
    $params = array(
      'appid' => Config::get('YAHOO'),
      'documentType' => 'text/plain',
      'documentContent' => $text,
    );
    return $this->_placemaker($params);
  }
  
  # http://developer.yahoo.com/geo/placemaker/
  function placemaker_html($html){
    $params = array(
      'appid' => Config::get('YAHOO'),
      'documentType' => 'text/html',
      'documentContent' => $html,
    );
    return $this->_placemaker($params);
  }

  function _placemaker($params){
    $this->annotations = array();
    
    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://wherein.yahooapis.com/v1/document', array(), 'xml', $http);
    
    if (!is_object($this->data))
      return FALSE;
     
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
      $this->annotations[] = array(
        'start' => (int) $item->start, 
        'end' => (int) $item->end, 
        'type' => 'place',
        'text' => (string) $item->text, 
        'entity' => $this->entities[(string) $item->woeIds],
        );
  }
  
  function terms($text, $context = ''){
    $params = array(
      'context' => $text,
      'query' => $context, // context for extraction (search terms)
      'output' => 'json',
      'appid' => Config::get('YAHOO'),
    );

    $http = array('method' => 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
    $this->get_data('http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction', array(), 'json', $http);

    $this->annotations = $this->data->ResultSet->Result;
  } 
}
