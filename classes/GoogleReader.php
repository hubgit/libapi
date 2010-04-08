<?php

class GoogleReader extends Google {
  public $doc = 'http://reader.google.com/';
  
  $this->n = 100;

  function content_by_feed($feed){
    $this->authorise();
            
    $headers = array(
      //'Authorization: GoogleLogin auth=' . $this->token,
      'Cookie: ' . $this->cookie,
      );
    
    $http = array('header' => $this->headers($headers));  
    $continuation = '';
  
    do{
      $this->get_data('http://www.google.com/reader/atom/feed/' . urlencode($query), array(
        'n' => $this->n,
        'c' => $continuation,
        ), 'dom', $http);
      
      $this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
      $this->xpath->registerNamespace('gr', 'http://www.google.com/schemas/reader/atom/');
    
      foreach ($this->xpath->query('/atom:feed/atom:entry') as $node){
        if ($this->output_dir)
          $this->data->save(sprintf('%s/%s.xml', $this->output_dir, base64_encode($item->id)), $node); 
        else
          $this->results[] = $node;
      }

      $continuation = $this->xpath->query('/atom:feed/gr:continuation')->item(0)->textContent;
      debug('Continuation: ' . $continuation);
    
      //sleep(1);

    } while ($continuation);
  }
}
