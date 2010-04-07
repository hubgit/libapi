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
    $this->api->stop($this->stopcode);
    $this->assertEquals(1, count($this->api->data->Stops));
    $this->assertEquals(13, count($this->api->data->Routes));
  }
  
  public function testRoute(){
    $this->api->route($this->route, $this->run);
    $this->assertEquals(33, count($this->api->data->Stops));
    $this->assertEquals(2, count($this->api->data->Routes));
  }
  
  public function testSearch(){
    $this->api->route_search($this->latitude, $this->longitude);
    debug($this->api->results);
    $this->assertEquals(8, count($this->api->results->Routes));
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $this->api->results[0]->CoOrds);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $this->api->results[0]->Count);
    
    $this->assertEquals($this->latitude, $result->CoOrds->Latitude);
    $this->assertEquals($this->longitude, $result->CoOrds->Longitude);
  }
  
  public function testTimetable(){
    return TRUE; // FIXME
    $this->api->timetable($this->stopcode,$this->route);
    $this->assertEquals(3, count($this->api->data));
    $this->assertEquals(4, count($this->api->data['Monday - Friday']));
    $this->assertEquals('<span class="ftbold">06</span>', $this->api->data['Monday - Friday']['First buses']);
  }
}
