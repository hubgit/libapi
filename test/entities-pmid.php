<?php

require '../main.inc.php';

$q = 16049137;

$api = new API('entities_pmid');

$responses = $api->all(array('pmid' => $q));
debug($responses);
