<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class AlchemyTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Alchemy;
    $this->text = file_get_contents(dirname(__FILE__) . '/text/news.txt');
  }
  
  public function testAnnotate(){
    $this->api->annotate($this->text);
    $this->assertEquals(30, count($this->api->entities));
  }
}