<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class YahooTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Yahoo;
  }
  
  public function testPageData(){
    $this->api->pagedata('http://www.google.com/');
    $this->assertEquals(10, count($this->api->results));
  }
}