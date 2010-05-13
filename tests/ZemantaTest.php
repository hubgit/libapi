<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class ZemantaTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Zemanta;
    $this->text = file_get_contents(dirname(__FILE__) . '/text/news.txt');
  }
  
  public function testSimilar(){
    $n = 20;
    $this->text = "Trying out a bit of Simon Willison's Redis Tutorial (and 'Node.js is genuinely exciting' post): this node.js script reads the HTTP referer header from a request, increments appropriate counters in a Redis database, then returns a 1x1px GIF image.";
    $this->api->similar($this->text, $n);
    $this->assertEquals($n, count($this->api->results));
    debug($this->api->results);
  }
  
  public function testKeywords(){
    $this->api->keywords($this->text);
    $this->assertEquals(8, count($this->api->results));  
  }
}