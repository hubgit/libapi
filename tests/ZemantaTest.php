<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class ZemantaTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Zemanta;
    $this->text = file_get_contents(dirname(__FILE__) . '/text/news.txt');
  }
  
  public function testSimilar(){
    $n = 20;
    $this->api->similar($this->text, $n);
    $this->assertEquals($n, count($this->api->results));
    debug($this->api->results);
  }
  
  public function testKeywords(){
    $this->api->keywords($this->text);
    $this->assertEquals(8, count($this->api->results));  
  }
}