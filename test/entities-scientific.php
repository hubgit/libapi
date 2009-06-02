<?php

require '../main.inc.php';

$text = <<<END
The early vertebrate skeletal muscle is a well-organized tissue in which the primitive muscle fibres, the myocytes, are all parallel and aligned along the antero-posterior axis of the embryo. How myofibres acquire their orientation during development is unknown. Here we show that during early chick myogenesis WNT11 has an essential role in the oriented elongation of the myocytes. We find that the neural tube, known to drive WNT11 expression in the medial border of somites1, is necessary and sufficient to orient myocyte elongation. We then show that the specific inhibition of WNT11 function in somites leads to the disorganization of myocytes. We establish that WNT11 mediates this effect through the evolutionary conserved planar cell polarity (PCP) pathway, downstream of the WNT/beta-catenin-dependent pathway, required to initiate the myogenic program of myocytes and WNT11 expression. Finally, we demonstrate that a localized ectopic source of WNT11 can markedly change the orientation of myocytes, indicating that WNT11 acts as a directional cue in this process. All together, these data show that the sequential action of the WNT/PCP and the WNT/beta-catenin pathways is necessary for the formation of fully functional embryonic muscle fibres. This study also provides evidence that WNTs can act as instructive cues to regulate the PCP pathway in vertebrates.
END;

$api = new API($text);
$responses = $api->all('entities');
debug($responses);
