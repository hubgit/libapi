<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class ChemSpiderTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new ChemSpider;
    
    // http://www.nature.com/nchem/journal/v1/n7/compound/nchem.351_comp1-epi.html
    $this->sid = 85098522;
    $this->cid = 44141874;
    $this->inchikey = 'LFQSCWFLJHTTHZ-UHFFFAOYSA-N';
    $this->inchi = 'InChI=1S/C2H6O/c1-2-3/h3H,2H2,1H3';
    $this->compoundName = 'ethanol';
    $this->csid = 682;
    
    $this->molfile = dirname(__FILE__) . '/text/ethanol.mol';
    $this->wikipedia = 'http://en.wikipedia.org/wiki/Ethanol';
  }
  
  public function testGetStructureSynonyms(){
    $result = $this->api->GetStructureSynonyms(file_get_contents($this->molfile));
    $this->assertGreaterThan(20, count($result));
  }
  
  public function testGetImage(){      
    $image = $this->api->get_image(array(
      'width' => 100,
      'height' => 100,
      'id' => $this->csid,
      'token' => Config::get('CHEMSPIDER'),
      ));
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_STRING, $image);
    $this->assertGreaterThan(100, strlen($image));
  }
  
  public function testSearch(){
    $result = $this->api->search(array('query' => sprintf('"%s"', $this->compoundName)));
    $this->assertEquals($this->csid, $result[0]);
    $this->assertEquals(1, count($result));    
  }
  
  public function testCSID2ExtRefs(){
    $result = $this->api->CSID2ExtRefs(array('csid' => $this->csid, 'datasources' => array('Wikipedia')));
    debug($result);
    $this->assertEquals($this->csid, $result['CSID']);    
    $this->assertEquals($this->wikipedia, $result['ext_url']);    
  }
  
  public function testInChIKeyToCSID(){
    $result = $this->api->InChIKeyToCSID($this->inchikey);
    $this->assertEquals($this->csid, $result);
  }
  
  public function testInChIToCSID(){
    $result = $this->api->InChIToCSID($this->inchi);
    $this->assertEquals($this->csid, $result);    
  }
}
