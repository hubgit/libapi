<?php

require '../main.inc.php';

$q = array(
  //'doi' => '10.1038/nature07564',
  'woeid' => 2487956,
  );

//$api = new API('metadata');
$api = new API('metadata', 'yahoo_geo');
$responses = $api->run($q);
debug($responses);
