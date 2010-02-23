<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TFLTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new TFL;
    $this->stopcode = 175;
    $this->route = 10;
    $this->run = 1;
    $this->latitude = 51.533;
    $this->longitude = -0.205;
  }
  
  public function testStop(){
    $result = $this->api->stop(array('stopcode' => $this->stopcode));
    //debug($result);
    $this->assertEquals(1, count($result->Stops));
    $this->assertEquals(13, count($result->Routes));
  }
  
  public function testRoute(){
    $result = $this->api->route(array('route' => $this->route, 'run' => $this->run));
    debug($result);
    $this->assertEquals(35, count($result->Stops));
    $this->assertEquals(2, count($result->Routes));
  }
  
  public function testSearch(){
    $result = $this->api->route_search(array('latitude' => $this->latitude, 'longitude' => $this->longitude));
    debug($result);
    $this->assertEquals(7, count($result->Routes));
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result->CoOrds);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result->Count);
    
    $this->assertEquals($this->latitude, $result->CoOrds->Latitude);
    $this->assertEquals($this->longitude, $result->CoOrds->Longitude);
  }
  
  public function testTimetable(){
    $result = $this->api->timetable(array('stopcode' => $this->stopcode, 'route' => $this->route));
    $this->assertEquals(3, count($result));
    $this->assertEquals(4, count($result['Monday - Friday']));
    $this->assertEquals('<span class="ftbold">06</span>', $result['Monday - Friday']['First buses']);
  }
}
