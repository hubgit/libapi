<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class WorldCatTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new WorldCat();
    $this->q = 'civil war';
  }

  public function testSearch(){
    list($results, $meta) = $this->api->search($this->q);
    //debug($meta);
    debug($results);
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $meta);
          
    $this->assertGreaterThan(300000, $meta['total']);
  }
}