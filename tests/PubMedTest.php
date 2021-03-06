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
    $data = $this->api->search($this->query, array('RetMax' => 1));

    $this->assertEquals(1, $this->api->total);
    $this->assertEquals($this->query, $data->QueryTranslation);
    $this->assertEquals($this->pmid, $data->IdList->Id[0]);
  }

  public function testFetch(){
    $this->api->fetch($this->pmid);

    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $this->api->data);
    //$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $this->api->xpath->query('PubmedArticle'));
    //$this->assertEquals($this->pmid, $this->api->xpath->query("PubmedArticle/MedlineCitation/PMID")->item(0)->nodeValue);
  }

  public function testContent(){
    $this->api->content('"Nature"[TA]', 5, 1);
    $this->assertEquals(10, count($this->api->results));
  }
}

