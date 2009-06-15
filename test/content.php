<?php

require '../main.inc.php';

$q = array(
  //'title' => 'Google',
  //'nytimes-facet' => 'SCIENCE AND TECHNOLOGY',
  'guardian-filter' => '/science',
  'output' => '../data/guardian',
  );

//$api = new API('content');
$api = new API('content', 'guardian');
$responses = $api->all($q);
debug($responses);
