<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('text/biology.txt'), 
  'pmid' => 17906637,
);

$api = new API('entities', array(
  'whatizit', 
  'gopubmed', 
  'ihop'
  ));
  
$responses = $api->all($q);

debug($responses);


