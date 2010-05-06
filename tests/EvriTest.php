<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class EvriTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Evri();
    $this->text = file_get_contents(dirname(__FILE__) . '/text/news.txt');
  }
  
  public function testConcepts(){
    $this->api->annotate($this->text);
    $this->assertEquals(27, count($this->api->entities));
  }
}