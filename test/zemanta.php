<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('text/news.txt'), 
);

$api = new API('entities', 'zemanta');
$responses = $api->run($q);
debug($responses);
