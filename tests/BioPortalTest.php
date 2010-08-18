<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class BioPortalTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new BioPortal;
    $this->text_dir = dirname(__FILE__) . '/text/';
    $this->text = file_get_contents($this->text_dir . 'biology.txt');
  }
  
  public function testAnnotate(){
    $text = mb_substr($this->text, 0, 5000);
    $this->api->annotate($text, array(42919, 42878)); // 42878 = CHEBI, 42919 = PRotein Ontology
    //$this->api->annotate($text, array(42919));
    debug($this->api->annotations);
    //debug($this->api->data->saveXML());
    //$this->assertEquals(29, count($this->api->annotations));
    
    $text = strtolower($text);
    
    foreach ($this->api->annotations as $annotation)
      $this->assertEquals(strtolower($annotation['text']), mb_substr($text, $annotation['start'] - 1, $annotation['end'] - ($annotation['start'] - 1)));
  }
}