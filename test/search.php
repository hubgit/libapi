<?php

require '../main.inc.php';

$q = 'anatomy';

$api = new API('search', 'guardian');
$responses = $api->run($q);
debug($responses);
