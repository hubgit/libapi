<?php

require '../main.inc.php';

$q = 'anatomy';

$api = new API('search');
$responses = $api->all($q);
debug($responses);
