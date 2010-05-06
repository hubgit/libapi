<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class DBPediaTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new DBPedia();
  }

  public function testSearch(){  
    $this->api->search('London');   
    $this->assertEquals(10, $this->api->results->length);
  }
  
}