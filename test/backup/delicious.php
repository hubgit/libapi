<?php

require dirname(__FILE__) . '/../../main.inc.php';

$user = 'hublicious';

$api = new API('content', 'delicious_user');
$api->run(array('output' => '/delicious/user/' . $user));

