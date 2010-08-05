<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class CHEBITest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new CHEBI;
    $this->term = 'ethanol';
  }

  public function testSearch(){
    $items = $this->api->search($this->term, 0, 10);
    $this->assertEquals(10, $this->api->total);
    $this->assertEquals(10, count($items));
    return $items;
  }
  
  /**
  * @depends testSearch
  */
  public function testFetch($items){
    $ids = array();
    foreach ($items as $item)
      $ids[] = $item->chebiId;
      
    $items = $this->api->fetch($ids);
    $this->assertEquals(10, count($items));
  }
}

