<?php

require '../main.inc.php';

$pmid = 19390618;

$api = new API($pmid);

$responses = $api->all('entities_pmid');
debug($responses);
