<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class ClassesTest extends PHPUnit_Framework_TestCase {  
  public function testClasses(){
    $files = glob(dirname(__FILE__) . '/../classes/*.php');
    foreach ($files as $file)
      require_once($file);
  }
}