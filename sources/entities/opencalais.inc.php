<?php

#

return defined('OPENCALAIS_KEY');

function entities_opencalais($text){
  $params = array(
    'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
    'licenseID' => OPENCALAIS_KEY,
    'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
      <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:calculateRelevanceScore="true" c:enableMetadataType="GenericRelations"/>
      <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
      <c:externalMetadata/>
      </c:params>',
  );
  
  $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
  
  $json = get_data('http://api.opencalais.com/enlighten/rest/', array(), 'json', $http);
  
  //debug($json);
  
  if (!is_object($json))
    return array();
  
  $entities = array();
  $references = array();
  foreach ($json as $id => $data){
    if ($id == 'doc' || $data->{'_typeGroup'} != 'entities')
      continue;
      
    $entities[$data->{'_type'}][$id] = array(
      'title' => $data->name,
      'score' => $data->relevance,
      );
    
    foreach ($data->instances as $instance){
      $references[] = array(
        'start' => $instance->offset,
        'end' => $instance->offset + $instance->length,
        'text' => $instance->exact,
        'entity' => $id,
        );
    }
  }
  
  return array($entities, $references);
}

