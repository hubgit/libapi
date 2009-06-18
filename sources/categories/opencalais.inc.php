<?php

# http://opencalais.com/documentation/calais-web-service-api

return defined('OPENCALAIS_KEY');

function categories_opencalais($q){
  if (!$text = $q['text'])
    return FALSE;
    
  $params = array(
    'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
    'licenseID' => OPENCALAIS_KEY,
    'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
      <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:enableMetadataType="SocialTags"/>
      <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
      <c:externalMetadata/>
      </c:params>',
  );
    
  $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
  $json = get_data('http://api.opencalais.com/enlighten/rest/', array(), 'json', $http);
  
  debug($json);
  
  if (!is_object($json))
    return array();
      
  $categories = array();
      
  foreach ($json as $id => $data){
    if ($id == 'doc')
      continue;
      
    switch($data->{'_typeGroup'}){
      case 'topics':
        $categories[$data->category] = array(
          'title' => $data->categoryName,
          'raw' => $data,
          );
      break;
      
      case 'socialTag':
        $categories[$data->socialTag] = array(
          'title' => $data->name,
          'score' => $data->importance,
          'raw' => $data,
          );      
      break;
    }    
  }
  
  return $categories;
}
