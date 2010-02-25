<?php

class Canonical extends API {
  public $doc = 'http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html';
  // TODO: cononical short URL

  function metadata($args){  
    $this->validate($args, 'url'); extract($args);

    $curl = curl_init($url);

    curl_setopt_array($curl, array(
      CURLOPT_CONNECTTIMEOUT => 10, // 10 second timeout
      CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
      CURLOPT_NOBODY => TRUE, // uses HEAD
    ));

    $result = curl_exec($curl);
    
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
      return false;
  
    $url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);  
    $mime = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
  
    if (strstr($mime, 'html')){
      $dom = @DOMDocument::loadHTMLFile($url); // fetch the HTML
      if (!is_object($dom))
        return $url; // return error?
        
      $xpath = new DOMXPath($dom);
      $links = $xpath->query("//head/link[@rel='canonical']/@href");
      if (!empty($links))
        $url = $links->item(0)->nodeValue;
    }  
  
    curl_close($curl);
    return $url;
  }
}
