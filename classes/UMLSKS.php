<?php

class UMLSKS extends API {
  static $release = '2007AC';
  static $pgt_file = '/tmp/proxy-grant-ticket.txt';
  
  function init(){         
    $this->auth_client = new SoapClient(
      'http://umlsks.nlm.nih.gov/authorization/services/AuthorizationPort?wsdl', 
      array(
        'local_cert' => UMLSKS_CERT,
        'allow_self_signed' => TRUE,
        'trace' => TRUE,
        ));
        
    $this->client = new SoapClient(
      'http://kswebp2.nlm.nih.gov/UMLSKS/services/UMLSKSService?wsdl', 
      array(
        'local_cert' => UMLSKS_CERT,
        'allow_self_signed' => TRUE,
        'trace' => TRUE,
        ));
       
    $this->pgt = file_exists($this->pgt_file) ? file_get_contents($this->pgt_file) : $this->get_pgt(); 
  }
    
  function get_pgt(){  
    $auth = explode(':', Config::get('UMLSKS_AUTH'));
    
    try{
      $result = $this->auth_client->getProxyGrantTicket($auth[0], $auth[1]);
    } catch (SoapFault $exception) { soap_exception($exception); }

    if (!$result)
      die('Could not fetch a proxy granting ticket');
      
    file_put_contents($this->pgt_file, $result); // save proxy granting ticket for subsequent use
    
    return $result;
  }
  
  function get_pt(){
    try{
      $result = $this->auth_client->getProxyTicket($this->pgt, 'http://umlsks.nlm.nih.gov');
    } catch (SoapFault $exception) { soap_exception($exception); }
    
    if (!$result)
      die('Could not fetch a proxy ticket');

    return $result;
  }
  
  function query($method, $params = array()){
    $params['casTicket'] = $this->get_pt();
    $params['release'] = $this->release;
    
    try{
      $response = call_user_func(array($this->client, $method), $params);
    } catch (SoapFault $exception) { soap_exception($exception); }
    
    return $response->contents;
  }
  
  function listFunctions(){
    return $this->client->__getFunctions();
  }
  
  function getCurrentUMLSVersion(){
    return $this->query('getCurrentUMLSVersion');
  }
  
  function listDictionaries(){
    return $this->query('listDictionaries');
  }
  
  function findCUIByExact($text){
    $params = array(      
      'searchString' => $text,
      'CVF' => 256,
      'includeSuppressibles' => 1,
      );
        
    return $this->query('findCUIByExact', $params);
  }
  
  function findCUIByApproximateMatch($text){
    $params = array(      
      'searchString' => $text,
      'CVF' => 256,
      'includeSuppressibles' => 1,
      );
        
    return $this->query('findCUIByApproximateMatch', $params);
  }
  
  function suggestSpelling($text){
    $params = array(      
      'term' => $text,
      );
        
    return $this->query('suggestSpelling', $params);
  }
  
  function getMeSHEntries($cui = NULL, $text = NULL){
    $params = array(      
      'CUI' => $cui,
      'term' => $text,
      'CVF' => 256,
      'includeSuppressibles' => 1,
      );
        
    return $this->query('getMeSHEntries', $params);
  }
  
  function soap_exception($e){
    debug($e);
    exit();
  }
}