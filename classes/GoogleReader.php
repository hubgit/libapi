<?php

class GoogleReader extends Google {
  public $doc = 'http://reader.google.com/';

  function content_by_feed($q){
    if (!$query = $q['feed'])
      return FALSE;
    
    if (isset($q['output']))
      $this->output_dir = $this->get_output_dir($q['output']);

    $this->authorise();
            
    $headers = array(
      //'Authorization: GoogleLogin auth=' . $this->token,
      'Cookie: ' . $this->cookie,
      );
    
    $http = array('header' => $this->headers($headers));
  
    $n = 100;
  
    $items = array();
    $continuation = '';
  
    do{
      $xml = $this->get_data('http://www.google.com/reader/atom/feed/' . urlencode($query), array(
        'n' => $n,
        'c' => $continuation,
        ), 'xml', $http);

      if (!is_object($xml))
        break;
      
      $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
      $xml->registerXPathNamespace('gr', 'http://www.google.com/schemas/reader/atom/');
    
      foreach ($xml->xpath('/atom:feed/atom:entry') as $item){
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.xml', $this->output_dir, base64_encode($item->id)), $item->asXML()); 
        else
          $items[] = $item;
      }

      $continuation = (string) current($xml->xpath('/atom:feed/gr:continuation'));
      debug('Continuation: ' . $continuation);
    
      //sleep(1);

    } while ($continuation);
  
    return $items;
  }
}
