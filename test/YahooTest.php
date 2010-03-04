<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class YahooTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Yahoo;
  }
  
  public function testPageData(){
    list($items, $meta) = $this->api->pagedata('http://www.google.com/');
    debug($items);
    $this->assertEquals(10, count($items));
  }
}