<?php

class OpenCalais extends API {
   public $doc = 'http://opencalais.com/documentation/calais-web-service-api';
   public $def = 'OPENCALAIS';
   
   public $annotations = array();
   public $categories = array();
   
   function query($params){
      $http = array('method'=> 'POST', 'content' => http_build_query($params), 'header' => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
      $this->get_data('http://api.opencalais.com/enlighten/rest/', array(), 'json', $http);
   }
   
   function annotate($text){
     $this->query(array(
        'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
        'licenseID' => Config::get('OPENCALAIS'),
        'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
          <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:calculateRelevanceScore="true" c:enableMetadataType="GenericRelations"/>
          <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
          <c:externalMetadata/>
          </c:params>',
      ));

     foreach ($this->data as $id => $data){
       if ($id == 'doc' || $data->{'_typeGroup'} != 'entities')
         continue;

       foreach ($data->instances as $instance){
         $this->annotations[] = array(
           'start' => $instance->offset,
           'end' => $instance->offset + $instance->length,
           'text' => $instance->exact,
           'type' => $data->{'_type'},
           'data' => array(
              'title' => $data->name,
              'score' => $data->relevance,
              'raw' => $data,
              ),
           );
       }
     }
   }
   
   function categorise($text){
     $this->query(array(
       'content' => sprintf('<Document><Body>%s</Body></Document>', htmlspecialchars($text)),
       'licenseID' => Config::get('OPENCALAIS'),
       'paramsXML' => '<c:params xmlns:c="http://s.opencalais.com/1/pred/">
         <c:processingDirectives c:contentType="text/xml" c:outputFormat="application/json" c:enableMetadataType="SocialTags"/>
         <c:userDirectives c:allowDistribution="false" c:allowSearch="false"/>
         <c:externalMetadata/>
         </c:params>', // does this work with a default namespace yet?
     ));
     
     foreach ($this->data as $id => $data){
       if ($id == 'doc')
         continue;

       switch ($data->{'_typeGroup'}){
         case 'topics':
           $this->categories[$data->category] = array(
             'title' => $data->categoryName,
             'raw' => $data,
             );
         break;

         case 'socialTag':
           $this->categories[$data->socialTag] = array(
             'title' => $data->name,
             'score' => $data->importance,
             'raw' => $data,
             );      
         break;
       }    
     }
   }
   
   function geocode($text){
     $this->annotate($text);
     
     $types = array('Organization', 'Facility', 'City', 'ProvinceOrState', 'Country');
     
     $address = array();
     foreach ($this->annotations as $annotation)
       if (in_array($annotation['type'], $types))
           $address[$annotation['type']] = $annotation['data']['title'];
     
     return array('address' => implode('; ', $this->sort_by_array($address, $types)));     
   }
   
   // FIXME: use array_multisort?
   function sort_by_array($input, $order) {
     $output = array();
     foreach ($order as $key)
       if (array_key_exists($key, $input))
         $output[$key] = $input[$key];
     return $output;
   }
}
