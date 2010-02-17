<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TFLTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new TFL;
    $this->stopcode = 175;
    $this->route = 10;
  }
  
  public function testStop(){
    $result = $this->api->stop_data(array('stopcode' => $this->stopcode));
    $this->assertEquals(1, count($result->Stops));
    $this->assertEquals(13, count($result->Routes));
  }
  
  public function testTimetable(){
    $result = $this->api->stop_route_timetable(array('stopcode' => $this->stopcode, 'route' => $this->route));
    $this->assertEquals(3, count($result));
    $this->assertEquals(4, count($result['Monday - Friday']));
    $this->assertEquals('<span class="ftbold">06</span>', $result['Monday - Friday']['First buses']);
  }
}
