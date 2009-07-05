<?php

require __DIR__ . '/../../main.inc.php';

$user = 'twalf';

$api = new API('content', 'twitter_user');
$api->run(array(
  'output' => '/twitter/user/' . preg_replace('/\W/', '_', $user),
  'user' => $user,
  ));

