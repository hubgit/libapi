<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class AmazonTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new Amazon();
  }

  public function testSearchMP3(){
    $result = $this->api->search('MP3Downloads', 'Nirvana - Nevermind');
  }
}