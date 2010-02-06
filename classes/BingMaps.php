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
    $client = new SoapClient($this->server . '/geocodeservice/geocodeservice.svc?wsdl');
    $request = $this->request($text, 1);
    $result = $client->Geocode($request);
   
    debug($result); //exit();

    if (!is_object($result) || $result->GeocodeResult->ResponseSummary->StatusCode != 'Success')
      return FALSE;

    $data = $result->GeocodeResult->Results->GeocodeResult;
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
  
  function search($q){
    if (!$text = $q['text'])
      return false;
    
    $n = isset($q['n']) ? $q['n'] : 10;
    
    $client = new SoapClient($this->server . '/searchservice/searchservice.svc?wsdl');
    $request = $this->request($text, $n);
    $result = $client->Search($request);
        
    if (!is_object($result) || $result->SearchResult->ResponseSummary->StatusCode != 'Success')
      return FALSE;
      
    //debug($result);
    
    return array($result->SearchResult->ResultSets->SearchResultSet->Results->SearchResultBase, array('raw' => $result));
  }
  
  function parse($q){
    $q['n'] = 1;
    $result = $this->search($q);
    
    if (!is_array($result->SearchResult->ResultSets->SearchResultSet))
      $result->SearchResult->ResultSets->SearchResultSet = array($result->SearchResult->ResultSets->SearchResultSet);
      
    //debug($result->SearchResult->ResultSets->SearchResultSet);
    
    $parsed = $result->SearchResult->ResultSets->SearchResultSet[0]->Parse;
    return array('keyword' => $parsed->Keyword, 'address' => $parsed->Address->FormattedAddress);
  }
}
