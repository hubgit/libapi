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
    $record = $this->api->metadata(array('doi' => $this->doi));
    //debug($record);
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $record);
    
    foreach (array('journal_metadata', 'journal_issue', 'journal_article') as $key)
      $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $record->{$key});
      
    $this->assertEquals($this->doi, (string) $record->{'journal_article'}->{'doi_data'}->doi);
    return $record;
  }
  
  public function testCitedBy(){
    list($items, $meta) = $this->api->citedby(array('doi' => $this->doi));
    
    //debug($items);
    //debug($meta);
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $items);
    $this->assertArrayHasKey('10.1038/nchem.381', $items);

    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $meta);
    $this->assertGreaterThan(0, $meta['total']);
    
    return $items;
  }
}