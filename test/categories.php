<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('text/news.txt'), 
  'pmid' => 18464,
);

$api = new API('categories', 'opencalais');
$responses = $api->all($q);
debug($responses);