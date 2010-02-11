<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TwitterTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $auth = explode(':', Config::get('TWITTER_AUTH'));
    $this->username = $auth[0];
    
    $this->api = new Twitter();
  }
  
  public function testFollowers(){
    $items = $this->api->followers(array('user' => $this->username));
    $this->assertEquals(128, count($items));
  }
  
  public function testFriends(){
    $items = $this->api->friends(array('user' => $this->username));
    $this->assertEquals(128, count($items));
  }

  public function testContentByUser(){
    $items = $this->api->content_by_user(array('user' => $this->username, 'max' => 5, 'from' => 1));
    $this->assertEquals(5, count($items));
  }
}