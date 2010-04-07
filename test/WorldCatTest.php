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
    $this->api->search($this->q);

    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $this->api->results);
    $this->assertGreaterThan(300000, $this->api->total);
  }
}