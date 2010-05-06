<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class BingMapsTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new BingMaps();
  }
  
  public function testSearch(){
    list($results, $meta) = $this->api->search(array('text' => 'Microsoft near Redmond', 'n' => 3));    
    $this->assertEquals(3, count($results)); 
  }

  public function testGeocode(){
    $result = $this->api->geocode(array('text' => '1 Microsoft Way, Redmond, WA, United States', 'n' => 1));

    $this->assertEquals('1 Microsoft Way, Redmond, WA 98052-8300', $result['address']);
    $this->assertEquals(47.639747, $result['lat']);
    $this->assertEquals(-122.129731, $result['lng']);
  }
}