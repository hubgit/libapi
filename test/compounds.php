<?php

require '../main.inc.php';

$text = <<<END
Indeed, aldehyde 3 could be prepared straightforwardly in two steps from commercially available starting materials. Aldol condensation of 3-acetylfuran (4) with 2,2-dimethylhex-5-enal (5) gave enone 6 (Fig. 2), a compound previously also used in Krische's elegant racemic synthesis. A formal olefin functionalization of compound 6 was then achieved by catalytic cross-metathesis. The reaction of olefin 6 with 10 mol% of commercially available Grubbs second-generation catalyst in the presence of crotonaldehyde (8) led to the formation of aldehyde 3 with an 84% yield (for an alternative synthesis of 3, see ref. 22). With the more active catalyst 7 only 2 mol% was needed to obtain enal 3 in 90% yield.
END;

$api = new API($text);
$responses = $api->all('entities');
debug($responses);
