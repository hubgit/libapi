<?php

require '../main.inc.php';

$q = array(
  'text' => file_get_contents('compounds.txt'),
  );

$api = new API('entities', array('whatizit'));
$responses = $api->run($q);
debug($responses);
