<?php

require '../main.inc.php';

$q = array(
  //'doi' => '10.1038/nature07564',
  //'woeid' => 2487956,
  'uri' => 'http://hublog.hubmed.org/archives/001879.html',
  );

//$api = new API('metadata');
$api = new API('metadata', 'bitly');
$responses = $api->run($q);
debug($responses);
