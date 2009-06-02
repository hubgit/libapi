<?php

# http://isiwebofknowledge.com/products_tools/products/related/trlinks/
# requires authentication

function citedby_thomson($doi){
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
  $xml = get_data('https://ws.isiknowledge.com/esti/xrpc', NULL, 'xml', $http);
  
  //debug($xml);
  
  $xml->registerXPathNamespace('isi', 'http://www.isinet.com/xrpc41');
  
  $errors = $xml->xpath("isi:fn/isi:error");
  if (!is_object($xml) || !empty($errors))
    return array(FALSE, array(FALSE, FALSE));
  
  $output = array();
  foreach ($xml->xpath("isi:fn/isi:map/isi:map/isi:map[@name='WOS']/isi:val") as $value){
    switch((string) $value['name']){
      case 'timesCited':
        $output[0] = (int) $value;
      break;
      case 'citingArticlesURL':
        $output[1] = (string) $value;
      break;
    } 
  }
  return $output;
}

