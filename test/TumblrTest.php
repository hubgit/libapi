<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TumblrTest extends PHPUnit_Framework_TestCase {
  public function setUp(){    
    $this->api = new Tumblr();
  }

  public function testContent(){
    $items = $this->api->content_by_user(array(
      'user' => 'test', 
      'max' => 100,
      //'output' => 'tumblr/test', 
      ));
    $this->assertEquals(100, count($items));
    return $items;
  }
}