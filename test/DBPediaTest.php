<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class DBPediaTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new DBPedia();
  }

  public function testSearch(){  
    list($results, $meta) = $this->api->search(array('text' => 'London'));   
    debug($results);   
    $this->assertEquals(10, count($results));
  }
  
}