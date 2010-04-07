<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class LastFMTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new LastFM();
  }
  
  public function testAlbumSearch(){
    $this->api->call('album.search', array('album' => 'Nirvana - Nevermind'));
    $this->assertEquals(5, count($this->api->data->results->albummatches->album));
  }
  
  public function testMetroHypeTracks(){
    $this->api->call('geo.getmetrohypetrackchart', array('country' => 'united kingdom', 'metro' => 'london'));
    $this->assertEquals(50, count($this->api->data->toptracks->track));
  }
  
  public function testMetroHypeArtists(){
    $this->api->call('geo.getmetrohypeartistchart', array('country' => 'united kingdom', 'metro' => 'london'));
    $this->assertEquals(50, count($this->api->data->topartists->artist));
  }
}