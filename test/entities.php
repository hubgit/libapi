<?php

require '../main.inc.php';
$debug = 'PRINT';

$q = array(
  'file' => 'text/news.txt',
  'text' => file_get_contents('text/news.txt'), 
  //'pmid' => 18464,
);

$api = new API('entities', 'unlock');
$responses = $api->run($q);
debug($responses);
