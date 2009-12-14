<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class AmazonTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new Amazon();
  }

  public function testSearchMP3(){
    $results = array();
    
    $page = 1;
    do {
      list($items, $meta) = $this->api->search(array(
        'SearchIndex' => 'MP3Downloads', 
        'Keywords' => 'Nirvana - Nevermind',
        'ItemPage' => $page,
        ));
        
      if (!$items)
        break;
        
      foreach ($items as $key => $item)
        $results[$key] = $item;
        
    } while (++$page <=  $meta['pages']);
      
    $this->assertGreaterThan(20, $meta['total']);
    $this->assertEquals(count($results), $meta['total']);
  }
}