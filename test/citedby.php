<?php

require '../main.inc.php';

$doi = '10.1038/nature05432';

$api = new API($doi);
$responses = $api->all('citedby');
debug($responses);
