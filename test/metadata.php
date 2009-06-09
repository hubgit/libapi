<?php

require '../main.inc.php';

$q = array(
  'doi' => '10.1038/nature07564',
  );

$api = new API('metadata');
$responses = $api->all($q);
debug($responses);
