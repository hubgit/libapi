<?php

require __DIR__ . '/../../main.inc.php';
require 'flickr.inc.php';

$user = '35034348300@N01';

$api = new API('content', 'flickr_user');
$api->run(array(
  'output' => '/flickr/user/' . preg_replace('/\W/', '_', $user),
  'user' => $user,
  ));

