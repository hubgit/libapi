<?php

require '../main.inc.php';

$q = 18464;

$api = new API('entities');

$responses = $api->all(array('pmid' => $q));
debug($responses);
