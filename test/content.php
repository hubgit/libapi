<?php

require '../main.inc.php';

$doi = 'Google';

$api = new API($doi);
$responses = $api->all('content');
debug($responses);
