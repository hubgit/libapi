<?php

require '../main.inc.php';

$q = 'Google';

$doi = '10.1038/nature07564';

$api = new API('content');
$responses = $api->all(array(
  //'title' => $q, 
  'doi' => $doi,
  ));
debug($responses);
