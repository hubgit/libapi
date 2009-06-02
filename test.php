<?php

require 'main.inc.php';

$q = '1600 Amphitheatre Parkway, Mountain View, CA';

$api = new API($q);
$responses = $api->all('geocode');
debug($responses);
