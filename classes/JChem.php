<?php

class JChem extends API {
  public $doc = 'https://www.chemaxon.com/webservices/developersGuide.html#services';
  
  private $server = 'http://0.0.0.0:8080/axis2/services/';
  
  // https://www.chemaxon.com/webservices/soap/MolConvertWS.wsdl.html
  // http://www.chemaxon.com/jchem/marvin/help/applications/molconvert.html
  function convert($input, $format, $inputFormat = NULL){
    $params = array(
      'targetStructure' => $input, // MOL or SMILES
      'outputFormat' => $format,
      );
    
    $method = 'convert'; 
    if ($inputFormat){
      $params['inputFormat'] = $inputFormat;
      $method = 'convertSpecialInput';
    }
    
    $this->soap($this->server . 'MolConvertWS?wsdl', $method, $params);
  }
  
  // https://www.chemaxon.com/webservices/soap/StandardizerWS.wsdl.html
  // https://www.chemaxon.com/jchem/doc/user/StandardizerConfiguration.html
  function standardize(){
    
  }
}