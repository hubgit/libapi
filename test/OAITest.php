<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class OAITest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new OAI('http://www.nature.com/oai/request');
  }
  
  public function testGetSampleIdentifier(){
    $this->api->getSampleIdentifier();
    debug($result);
    $this->assertEquals('oai:nature.com:10.1038/187504a0', $this->api->sampleIdentifier);
  }
  
  public function testListSets(){
    $this->api->listSets();
    debug($this->api->sets);
    $this->assertEquals(182, count($this->api->sets));
  }
  
  public function testlListMetadataFormats(){
    $this->api->listMetadataFormats();
    debug($this->api->formats);
    $this->assertEquals(2, count($this->api->formats));
  }
  
  public function testListRecords(){
    $this->api->maxRecords = 10;
    $result = $this->api->listRecords('news', 'oai_dc');
    debug($this->api->records);
    $this->assertEquals(10, count($this->api->records));
  }
}
