<?php

require '../main.inc.php';

$q = '10.1038/nature05432';

$api = new API('citedby');
$responses = $api->all(array('doi' => $q));
debug($responses);
