<?php

require '../main.inc.php';

$q = 'Google';

$api = new API('content');
$responses = content_wikipedia(array('title' => $q));
debug($responses);
