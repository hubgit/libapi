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
    
    $text = file_get_contents(dirname(__FILE__) . '/text/biology.txt');    
    $uri = $this->api->upload($text, 'text/plain', 'Test Document');
    
    $this->assertEquals(201, $this->api->http_status);

    preg_match('!http://docs\.google\.com/feeds/id/(.+)!', $uri, $matches);
    $id = $matches[1];
    $this->assertFalse(empty($id));
    
    debug($id);

    return array($token, $id);
  }
  
  /**
   * @depends testUpload
   */
  public function testDelete(array $params){
    list($token, $id) = $params;
    $this->api->token = $token;
    
    $this->api->delete($id);
    $this->assertEquals(0, $this->api->http_status);
  }
  
  /**
   * @depends testAuthorise
   */
  public function testOCR($token){
    $this->api->token = $token;
    
    $png = file_get_contents(dirname(__FILE__) . '/text/biology.png');
    $result = $this->api->upload($png, 'image/png', 'Test OCR Image', array('ocr' => 'true'));
    
    $this->assertEquals(201, $this->api->http_status);
  }
}