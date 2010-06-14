<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class GoogleChartTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new GoogleChart;
  }
  
  public function testChart(){
    $this->api->chart(array(
      'type' => 'lc',
      'title' => 'Test Chart',
      'size' => '600x200',
      'datatype' => 't',
      'data' => array(1,5,10,15,20,35),
      'axes' => array('x' => '0,ff0000,12,0,lt', 'y' => '1,0000ff,20,1,lt')
      ));
      
    $this->assertEquals(1, count($this->api->results)); 
  }
}