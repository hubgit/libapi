<?php

class HTTPException extends Exception {
  function __construct($status, $message, $content = NULL){
    header(sprintf('HTTP/1.1 %d %s', $status, $message));
    if ($content)
      print $content;
    exit();
  }
}
