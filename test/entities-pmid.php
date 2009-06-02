<?php

require '../main.inc.php';

$pmid = 16049137;

$api = new API($pmid);

$responses = $api->all('entities_pmid');
debug($responses);
