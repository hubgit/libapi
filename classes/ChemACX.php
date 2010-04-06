<?php

class ChemACX extends API {
  public $doc = 'http://chemacx.cambridgesoft.com/';

  function content_chemacx($id){      
    $this->get_data('http://chemacx.cambridgesoft.com/chemacx/chemacx/chemacx_action.asp', array(
      'dbname' => 'chemacx',
      'dataaction' => 'get_structure',
      'Table' => 'Substance',
      'Field' => 'Structure',
      'DisplayType' => 'cdx',
      'StrucID' => $id,
    ), 'raw');
  }
}