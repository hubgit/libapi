<?php

require '../main.inc.php';

$q = '1600 Amphitheatre Parkway, Mountain View, CA';

$api = new API('geocode');
$responses = $api->all(array('address' => $q));
debug($responses);
