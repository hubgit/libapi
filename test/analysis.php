<?php

require '../main.inc.php';

$q = <<<END
Jacqui Smith is expected to stand down as home secretary in a reshuffle, Whitehall sources have told the BBC.
Prime Minister Gordon Brown is set to shake-up his cabinet after Thursday's European and English local elections.
Ms Smith has been criticised for listing her sister's London house as her main home for expenses - and her husband's claim for an adult movie.
It is understood Ms Smith, the first woman home secretary, intends to defend her Redditch seat at the next election.
Mr Brown confirmed to the BBC he is planning a reshuffle but refused to be drawn on individual ministers' roles.

The early vertebrate skeletal muscle is a well-organized tissue in which the primitive muscle fibres, the myocytes, are all parallel and aligned along the antero-posterior axis of the embryo. How myofibres acquire their orientation during development is unknown. Here we show that during early chick myogenesis WNT11 has an essential role in the oriented elongation of the myocytes. We find that the neural tube, known to drive WNT11 expression in the medial border of somites1, is necessary and sufficient to orient myocyte elongation. We then show that the specific inhibition of WNT11 function in somites leads to the disorganization of myocytes. We establish that WNT11 mediates this effect through the evolutionary conserved planar cell polarity (PCP) pathway, downstream of the WNT/beta-catenin-dependent pathway, required to initiate the myogenic program of myocytes and WNT11 expression. Finally, we demonstrate that a localized ectopic source of WNT11 can markedly change the orientation of myocytes, indicating that WNT11 acts as a directional cue in this process. All together, these data show that the sequential action of the WNT/PCP and the WNT/beta-catenin pathways is necessary for the formation of fully functional embryonic muscle fibres. This study also provides evidence that WNTs can act as instructive cues to regulate the PCP pathway in vertebrates.
END;

$api = new API('analysis');
$responses = $api->all(array('text' => $q));
debug($responses);
