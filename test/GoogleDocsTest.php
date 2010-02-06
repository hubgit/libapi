<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class GoogleDocsTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new GoogleDocs();
  }
  
  public function testAuthorise(){
    $this->api->authorise('writely');
    
    $this->assertFalse(empty($this->api->token));
    $this->assertFalse(empty($this->api->cookie));

    debug('Token: ' . $this->api->token);
    return $this->api->token;
  }

  /**
   * @depends testAuthorise
   */
  public function testUpload($token){
    $this->api->token = $token;
    
    debug('Uploading document');
    $data = file_get_contents(dirname(__FILE__) . '/text/biology.txt');    
    $id = $this->api->upload($data, 'text/plain', 'Test Document');
    
    $this->assertEquals(201, $this->api->http_status);
    $this->assertFalse(empty($id));

    debug('Deleting ' . $id);
    $this->api->delete($id);
    $this->assertEquals(200, $this->api->http_status);
  }
  
  /**
   * @depends testAuthorise
   */
  public function testOCR($token){
    $this->api->token = $token;
    
    debug('Uploading image for OCR');
    $data = file_get_contents(dirname(__FILE__) . '/text/biology.png');
    $id = $this->api->upload($data, 'image/png', 'Test OCR Image', array('ocr' => 'true'));
    
    debug('Deleting ' . $id);
    $this->assertEquals(201, $this->api->http_status);
    $this->assertFalse(empty($id));
    
    $this->api->delete($id);
    $this->assertEquals(200, $this->api->http_status);
  }
}