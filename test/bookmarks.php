<?php

require '../main.inc.php';

$q = 'http://www.nature.com/';

$api = new API('bookmarks');
$responses = $api->all(array('uri' => $q));
debug($responses);
