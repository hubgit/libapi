<?php

require '../main.inc.php';

$q = array(
  //'title' => 'Google',
  'facet' => 'SCIENCE AND TECHNOLOGY',
  'output' => '../data/nytimes',
  );

//$api = new API('content');
$api = new API('content', 'nytimes');
$responses = $api->all($q);
debug($responses);
