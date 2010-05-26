<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class TwitterTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $auth = explode(':', Config::get('TWITTER_AUTH'));
    $this->username = $auth[0];
    $this->api = new Twitter;
  }
  
  public function testFollowers(){
    $this->api->followers($this->username);
    $this->assertGreaterThan(10, count($this->api->results));
  }
  
  public function xtestFriends(){
    $this->api->friends($this->username);
    $this->assertGreaterThan(10, count($this->api->results));
  }

  public function xtestContentByUser(){
    $this->api->content_by_user($this->username, 5, 1);
    $this->assertGreaterThan(1, count($this->api->results));
  }
}