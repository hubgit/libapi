<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('text/web.txt'), 
  'pmid' => 18464,
);

$api = new API('entities', 'opencalais');
$responses = $api->all($q);
debug($responses);
