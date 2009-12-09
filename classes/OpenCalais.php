<?php

class OpenCalais extends API {
   public $doc = 'http://opencalais.com/documentation/calais-web-service-api';
   public $def = 'OPENCALAIS';
   
   function query($params){
      $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
      return$this->get_data('http://api.opencalais.com/enlighten/rest/', array(), 'json', $http);
   }
   
   function entities($q){
     if (!$text = $q['text'])
       return FALSE;

     $json = $this->query(array(
        'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
        'licenseID' => Libapi_Config::get('OPENCALAIS'),
        'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
          <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:calculateRelevanceScore="true" c:enableMetadataType="GenericRelations"/>
          <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
          <c:externalMetadata/>
          </c:params>',
      ));

    //debug($json);

     if (!is_object($json))
       return FALSE;

     $entities = array();
     $references = array();
     foreach ($json as $id => $data){
       if ($id == 'doc' || $data->{'_typeGroup'} != 'entities')
         continue;

       $entities[$data->{'_type'}][$id] = array(
         'title' => $data->name,
         'score' => $data->relevance,
         'raw' => $data,
         );

       foreach ($data->instances as $instance){
         $references[] = array(
           'start' => $instance->offset,
           'end' => $instance->offset + $instance->length,
           'text' => $instance->exact,
           'name' => $data->name,
           'type' => $data->{'_type'},
           'entity' => $id,
           'snippet' => sprintf('%s{{{%s}}}%s', $instance->prefix, $instance->exact, $instance->suffix),
           );
       }
     }

     return array($entities, $references);
   }
   
   function categories($q){
     if (!$text = $q['text'])
       return FALSE;

     $json = $this->query(array(
       'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
       'licenseID' => Libapi_Config::get('OPENCALAIS'),
       'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
         <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:enableMetadataType="SocialTags"/>
         <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
         <c:externalMetadata/>
         </c:params>', // does this work with a default namespace yet?
     ));

     //debug($json);

     if (!is_object($json))
       return false;

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
}
