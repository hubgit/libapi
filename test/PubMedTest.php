<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class PubMedTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new PubMed();
    $this->query = '"10.1038/nature04979"[DOI]';
    $this->pmid = 16862119;
  }
  
  public function testSearch(){
    $result = $this->api->search($this->query);
    
    $this->assertEquals(1, $this->api->count);
    $this->assertEquals($this->query, (string) $result->QueryTranslation);
    $this->assertEquals($this->pmid, (int) $result->IdList->Id);
  }

  public function testFetch(){
    $result = $this->api->fetch($this->pmid);
    
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result->{'PubmedArticle'});
    $this->assertEquals($this->pmid, (int) $result->PubmedArticle->MedlineCitation->PMID);
  }
  
  public function testContent(){
    $items = $this->api->content(array('term' => '"Nature"[TA]', 'max' => 5, 'from' => 1));
    $this->assertEquals(10, count($items));
  }
}
