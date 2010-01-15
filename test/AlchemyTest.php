<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class AlchemyTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Alchemy();
    $this->text = file_get_contents(dirname(__FILE__) . '/text/news.txt');
  }
  
  public function testEntities(){
    list($entities, $references) = $this->api->entities(array('text' => $this->text));
    
    $this->assertEquals(29, count($entities));
    $this->assertEquals(0, count($references));
  }
}