<?php

require '../main.inc.php';

$q = 'Google';

$api = new API('content');
$responses = $api->all(array('title' => $q));
debug($responses);
