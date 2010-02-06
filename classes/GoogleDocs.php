<?php

class GoogleDocs extends Google {
  public $doc = 'http://docs.google.com/';
  public $def = array('GOOGLE_AUTH');
  
  // FIXME: using curl rather than file_get_contents
  
  function upload($data, $type = 'text/plain', $title = 'Untitled Document', $params = array()){    
    $headers = array(
      'Content-Type' => $type,
      'Slug' => $title,
      );
    
    $http = array('method' => 'POST', 'content' => $data, 'header' => $this->headers($headers));
    $result = $this->get_data_curl('https://docs.google.com/feeds/default/private/full', $params, 'xml', $http);
    
    if (!isset($result->id))
      return false;
    
    preg_match('!/feeds/id/(.+)!', (string) $result->id, $matches);
    return $matches[1]; // document id
  }
  
  function delete($id){
    $http = array('method' => 'DELETE', 'header' => $this->headers(array('If-Match' => '*')));
    $result = $this->get_data_curl('https://docs.google.com/feeds/default/private/full/' . $id, array('delete' => 'true'), 'xml', $http);
    return $result;
  }
  
  /*
  function upload_multipart($data, $type = 'text/plain', $title = 'Untitled Document', $params = array()){    
    $boundary = '---------------------' . substr(md5(time()), 0, 10);
              
    $content = $this->multipart($boundary, array(
      'application/atom+xml' => '<entry xmlns="http://www.w3.org/2005/Atom"><category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/docs/2007#text/html"/></entry>',
      $type => $data,
      ));

    $headers = array(
      'Content-Type: multipart/related; boundary=' . $boundary,
      'Slug: ' . $title,
      );
    
    $http = array('method' => 'POST', 'content' => $content, 'header' => $this->headers($headers));
    $result = $this->get_data_curl('http://docs.google.com/feeds/default/private/full', $params, 'xml', $http);
    return (string) $result->id;
  }
  
  function multipart($boundary, $data){
    $items = array();
    foreach ($data as $type => $content)
      $items[] = "Content-Type: $type\n\n$content";
    
    $data = "--$boundary\n" . implode("\n--$boundary\n", $items) . "\n--$boundary--\n\n";
    return str_replace("\n", "\r\n", $data);
  }
  */
}
