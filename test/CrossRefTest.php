<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class CrossRefTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new CrossRef();
    $this->doi = '10.1038/nchem.351';
  }

  public function testRecord(){
    $record = $this->api->metadata($this->doi);

    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $record);
    
    foreach (array('journal_metadata', 'journal_issue', 'journal_article') as $key)
      $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $record->{$key});
      
    $this->assertEquals($this->doi, (string) $record->{'journal_article'}->{'doi_data'}->doi);
  }
  
  public function testCitedBy(){
    $this->api->citedby($this->doi);

    $this->assertArrayHasKey('10.1038/nchem.381', $this->api->results);
    $this->assertGreaterThan(0, $this->api->total);
  }
}