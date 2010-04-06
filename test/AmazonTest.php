<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class AmazonTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new Amazon;
  }

  public function testSearchMP3(){    
    $page = 1;
    do {
      $this->api->search(array(
        'SearchIndex' => 'MP3Downloads', 
        'Keywords' => 'Nirvana - Nevermind',
        'ItemPage' => $page,
        ));

    } while (++$page <= $this->api->pages);
      
    $this->assertGreaterThan(20, $this->api->total);
    $this->assertEquals(count($this->api->results), $this->api->total);
  }
  
  public function testSearchBooksUK(){
    $this->api->host = 'ecs.amazonaws.co.uk';
    
    $page = 1;
    do {
      $this->api->search(array(
        'SearchIndex' => 'Books', 
        'Author' => 'Tim Key',
        'ItemPage' => $page,
        ));        
    } while (++$page <= $this->api->pages);
      
    $this->assertGreaterThan(2, $this->api->total);
    $this->assertEquals(count($this->api->results), $this->api->total);
  }
}