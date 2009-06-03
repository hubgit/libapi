<?php

require '../main.inc.php';

$q = 16049137;

$api = new API('entities');

$responses = $api->all(array('pmid' => $q));
debug($responses);
