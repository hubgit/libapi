<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class GoogleMapsTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new GoogleMaps();
  }
  
  public function testSearch(){
    list($results, $meta) = $this->api->search(array('text' => 'Google near Mountain View'));
    $this->assertEquals(1, count($results)); 
  }

  public function testGeocode(){
    $result = $this->api->geocode('1600 Amphitheatre Parkway, Mountain View');

    $this->assertEquals('1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA', $result['address']);
    $this->assertGreaterThan(37, $result['lat']);
    $this->assertLessThan(-122, $result['lng']);
  }
}