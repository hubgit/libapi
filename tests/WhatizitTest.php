<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class WhatizitTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Whatizit;
    $this->text_dir = dirname(__FILE__) . '/text/';
    $this->text = file_get_contents($this->text_dir . 'biology.txt');
  }
  
  public function testAnnotate(){
    $this->api->annotate($this->text);
    debug($this->api->annotations);
    $this->assertEquals(61, count($this->api->annotations));
  }
}