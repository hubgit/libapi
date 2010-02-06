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
    $this->assertEquals(3, count($results)); 
  }

  public function testGeocode(){
    $result = $this->api->geocode('1600 Amphitheatre Parkway, Mountain View');

    $this->assertEquals('1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA', $result['address']);
    $this->assertEquals(37.421759, $result['lat']);
    $this->assertEquals(-122.08437, $result['lng']);
  }
}