<?php

require '../main.inc.php';

$text = <<<END
Jacqui Smith is expected to stand down as home secretary in a reshuffle, Whitehall sources have told the BBC.
Prime Minister Gordon Brown is set to shake-up his cabinet after Thursday's European and English local elections.
Ms Smith has been criticised for listing her sister's London house as her main home for expenses - and her husband's claim for an adult movie.
It is understood Ms Smith, the first woman home secretary, intends to defend her Redditch seat at the next election.
Mr Brown confirmed to the BBC he is planning a reshuffle but refused to be drawn on individual ministers' roles.
END;

$api = new API($text);
$responses = $api->all('entities');
debug($responses);
