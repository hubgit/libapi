<?php

require '../main.inc.php';

$q = array(
  //'uri' => 'http://www.nature.com/',
  'url' => 'http://tinyurl.com/mh24g9',
  );

$api = new API('metadata', 'canonical');
$responses = $api->run($q);

debug($responses);
