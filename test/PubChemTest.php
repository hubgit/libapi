<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class PubChemTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new PubChem();
    
    // http://www.nature.com/nchem/journal/v1/n7/compound/nchem.351_comp1-epi.html
    $this->sid = 85098522;
    $this->cid = 44141874;
    $this->inchikey = 'ZYQPHUOTSRRMDV-JAZMHMKJSA-N';
    $this->inchi = 'InChI=1S/C32H48O7/c1-11-25-19(4)28-20(5)26(36-31(37-28)23-12-14-24(34-10)15-13-23)17(2)16-18(3)27-21(6)29(22(7)30(33)35-25)39-32(8,9)38-27/h11-15,17-22,25-29,31H,1,16H2,2-10H3/t17-,18+,19+,20+,21-,22-,25+,26+,27+,28+,29+,31-/m1/s1';
    $this->term = 'ethanol';
  }
  
  public function testSearchCID(){
    $this->api->search(array('cid' => $this->cid));
    $this->assertEquals(1, $this->api->count);
    $result = $this->api->fetch();
  }
    
  public function testSearchInChIKey(){
    $result = $this->api->search(array('inchikey' => $this->inchikey));
    $this->assertEquals(1, $this->api->count);
    $result = $this->api->fetch();
  }
    
  public function testSearchSID(){
    $result = $this->api->search(array('sid' => $this->sid, 'db' => 'pcsubstance'));
    $this->assertEquals(1, $this->api->count);
    $result = $this->api->fetch();
  }
  
  public function testSearchTerm(){
    $result = $this->api->search(array('term' => sprintf('"%s"', $this->term)));
    $this->assertGreaterThan(90000, $this->api->count);
    $this->assertLessThan(250000, $this->api->count);
    
    $result = $this->api->fetch(NULL, array('retmax' => 3));
    return $result[0];
  }
  
  /*
  * depends testSearchTerm
  */
  public function testParse(){
    //$meta = $this->api->parse($this->doc);
  }
  
  public function testGetImage(){      
    $image = $this->api->get_image(array(
      'width' => 100,
      'height' => 100,
      'cid' => $this->cid,
      ));
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_STRING, $image);
  }
  
  /*
  public function testSearchInChI(){
    $result = $this->api->search(array('inchi' => $this->inchi));
    $this->assertEquals(1, $this->api->count);
    $result = $this->api->fetch();
  }
  */
}
