<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TumblrTest extends PHPUnit_Framework_TestCase {
  public function setUp(){    
    $this->api = new Tumblr();
  }

  public function testContent(){
    $this->api->content_by_user('test', 100);
    $this->assertEquals(100, count($this->api->results));
  }
}