<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class UniProtTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new UniProt;
  }
  
  public function testSearch(){
    $this->api->search('FOXP3');
    debug($this->api->results);
    $this->assertEquals(8, count($this->api->results));
    
  }
}