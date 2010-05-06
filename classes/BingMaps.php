<?php

class BingMaps extends API {
  public $doc = 'http://msdn.microsoft.com/en-us/library/cc980855.aspx';
  public $def = 'BING_MAPS';
  
  private $server = 'http://dev.virtualearth.net/webservices/v1';
    
  function request($query, $n = 10){
    return array('request' => array(
      'Credentials' => array('ApplicationId' => Config::get('BING_MAPS')),
      'Query' => $query,
      'Options' => array('Count' => $n),
    ));
  }

  function geocode($text){
    $params = $this->request($text, 1);
    $this->soap($this->server . '/geocodeservice/geocodeservice.svc?wsdl', 'Geocode', $params);
   
    if ($this->data->GeocodeResult->ResponseSummary->StatusCode != 'Success')
      return FALSE;

    $data = $this->data->GeocodeResult->Results->GeocodeResult;
    if (!is_array($data->Locations->GeocodeLocation))
      $data->Locations->GeocodeLocation = array($data->Locations->GeocodeLocation);
    $location = $data->Locations->GeocodeLocation[0];

    return array(
      'address' => $data->Address->FormattedAddress, 
      'lat' => (float) $location->Latitude, 
      'lng' => (float) $location->Longitude,
      'raw' => $data,
      );
  }
  
  function search($text, $n = 10){        
    $params = $this->request($text, $n);
    $this->soap($this->server . '/searchservice/searchservice.svc?wsdl', 'Search', $params);
        
    if (!$this->data->SearchResult->ResponseSummary->StatusCode != 'Success')
      return FALSE;
    
    $this->results = $result->SearchResult->ResultSets->SearchResultSet->Results->SearchResultBase;
  }
  
  function parse($args){
    $args['n'] = 1;
    $result = $this->search($args);
    
    if (!is_array($result->SearchResult->ResultSets->SearchResultSet))
      $result->SearchResult->ResultSets->SearchResultSet = array($result->SearchResult->ResultSets->SearchResultSet);
          
    $parsed = $result->SearchResult->ResultSets->SearchResultSet[0]->Parse;
    return array('keyword' => $parsed->Keyword, 'address' => $parsed->Address->FormattedAddress);
  }
}
