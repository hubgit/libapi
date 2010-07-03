<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class MendeleyTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new Mendeley;
  }
  
  public function testSearch(){
    $this->api->search('science');
    debug($this->api->data);     
    $this->assertEquals(141495, $this->api->total);
    $this->assertEquals(20, count($this->api->results));
  }
  
  public function testDetails(){
    $this->api->document_details('77aaab00-6d02-11df-afb8-0026b95d30b2');
    debug($this->api->data);        
    //$this->api->document_details('10.1038/nature09036', 'doi');
    //debug($this->api->data);    
  }
  
  public function testRelated(){
    $this->api->document_related('77aaab00-6d02-11df-afb8-0026b95d30b2');
    debug($this->api->data);  
    $this->assertEquals(9, $this->api->total);
    $this->assertEquals(9, count($this->api->results));       
  }
}