<?php

require '../main.inc.php';

$q = array(
  'uri' => 'http://www.nature.com/',
  );

$api = new API('bookmarks');
$responses = $api->all($q);
debug($responses);
