<?php

# http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html

function metadata_canonical($q){  
  if (!$url = $q['url'])
    return FALSE;
    
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
  
  //if (!$q['html']) // don't need to look for canonical links in the HTML
    //return $url;
  
  $mime = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
  
  if (strstr($mime, 'html')){
    $html = @DOMDocument::loadHTMLFile($url);
    // TODO: check if successful
    $xml = simplexml_import_dom($html);
    $links = $xml->xpath("//head/link[@rel='canonical']");
    if (!empty($links))
      $url = (string) $links[0]['href'];
  }  
  
  curl_close($curl);
  return $url;
}
