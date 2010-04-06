<?php

class ISI extends API {
  public $doc = 'http://isiwebofknowledge.com/products_tools/products/related/trlinks/'; # requires IP authentication

  function citedby($doi){    
    $query = sprintf('<map name="%s"><val name="doi">%s</val></map>', htmlspecialchars($doi), htmlspecialchars($doi));
  
    $request = sprintf('<?xml version="1.0" encoding="UTF-8" ?>
    <request xmlns="http://www.isinet.com/xrpc41">
      <fn name="LinksAMR.retrieve">
        <list>
          <!-- authentication -->
          <map><!-- leave this empty to use IP address for authentication --></map>

          <!-- what to to return -->
          <map>
            <list name="WOS">
              <val>timesCited</val>
              <val>citingArticlesURL</val>
            </list>
          </map>

          <!-- query -->
          <map>%s</map>
        </list>
      </fn>
    </request>
    ', $query);

    $http = array('method'=> 'POST', 'content' => $request, 'header' => 'Content-Type: text/xml');
    $this->get_data('https://ws.isiknowledge.com/esti/xrpc', NULL, 'dom', $http);

    $this->xpath->registerNamespace('isi', 'http://www.isinet.com/xrpc41');
  
    $nodes = $this->xpath->query("isi:fn/isi:error");
    if (!empty($nodes))
      return FALSE;
    
    $this->total = $this->xpath->query("isi:fn/isi:map/isi:map/isi:map[@name='WOS']/isi:val[@name='timesCited']")->item(0)->nodeValue;
    $this->url = $this->xpath->query("isi:fn/isi:map/isi:map/isi:map[@name='WOS']/isi:val[@name='citingArticlesURL']")->item(0)->nodeValue;
  }
}

