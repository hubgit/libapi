<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('text/news.txt'), 
  'pmid' => 18464,
  'title' => array('Google', 'Yahoo'),
);

$api = new API('categories', 'wikipedia');
$responses = $api->run($q);
debug($responses);
