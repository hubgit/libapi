<?php

require '../main.inc.php';

$q = array(
  'title' => 'Google',
  );

$api = new API('content');
$responses = $api->all($q);
debug($responses);
