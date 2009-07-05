<?php

# http://www.flickr.com/services/api/flickr.photos.getInfo.htm

return defined('FLICKR_KEY');

function metadata_flickr($q){    
  if (!$id = $q['id'])
   return FALSE;
    
  $data = get_data('http://api.flickr.com/services/rest/', array(
    'api_key' => FLICKR_KEY,
    'format' => 'php_serial',
    'method' => 'flickr.photos.getInfo',
    'photo_id' => $id,
    'secret' => $q['secret'],
    ), 'php');
  
  //debug($data);
  
  if (!is_array($data) || $data['stat'] != 'ok')
    return FALSE;
    
  return $data['photo'];
}
