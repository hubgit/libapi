<?php

class ChemSpider extends API {
    public $doc = 'http://www.chemspider.com/AboutServices.aspx';
    public $def = 'CHEMSPIDER';

    function InChIKeyToCSID($inchikey){
      $xml = $this->get_data('http://www.chemspider.com/InChI.asmx/InChIKeyToCSID', array('inchi_key' => $inchikey), 'xml');

      debug($xml);
      if (!is_object($xml))
        return FALSE;

      return (int) $xml;
    }
}