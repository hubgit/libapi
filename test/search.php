<?php

require '../main.inc.php';

$q = 'anatomy';

$api = new API('search', 'guardian');
$responses = $api->all($q);
debug($responses);
