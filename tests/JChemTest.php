<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class JChemTest extends PHPUnit_Framework_TestCase {
  public function setUp(){
    $this->api = new JChem;
    $this->mol = file_get_contents(dirname(__FILE__) . '/text/ethanol.mol');
  }

  public function testConvert(){
    return TRUE;
    $this->api->convert($this->mol, 'png');
    debug($this->api->data);
  }

  public function testConvertSpecialInput(){
    return TRUE;
    $this->api->convert($this->mol, 'png', 'mol');
    debug($this->api->data);
  }
}

