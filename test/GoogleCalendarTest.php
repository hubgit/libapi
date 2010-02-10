<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class GoogleCalendarTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new GoogleCalendar;
  }
  
  public function testContent(){
    $items = $this->api->content('schedule@resonancefm.com', array(
      'start-min' => strtotime('2009-02-01'),
      'start-max' => strtotime('2009-02-08'),
      'ctz' => 'Europe/London',
      'max-results' => 10,
      ));     
      
    debug($items);
    
    $this->assertEquals(10, count($items);
  }
}