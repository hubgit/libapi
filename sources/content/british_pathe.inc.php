<?php

# 

function content_british_pathe($q){
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
  
  $id = isset($q['start']) ? (int) $q['start'] : 1;
  
  $items = array();
  
  $attempts = array();
  
  global $http_status;
  
  do{      
    $html = get_data('http://www.britishpathe.com/record.php', array(
      'id' => $id,
      'view' => 'print',
    ), 'html', array('timeout' => 120));
    
    if ($http_status == 403)
      break;
    
    //debug($html);
    
    if ($http_status != 200){
      if (++$attempts[$id] > 2)
        $id++;
      continue;
    }
    
    //if (!is_object($html) || empty($html->body))
    if (empty($html->body))
     break;
      
    $flat = $html->asXML();
      
    if ($output_folder)
      file_put_contents(sprintf('%s/%d.html', $output_folder, $id), $flat); 
    else
      $items[] = $item;
    
    if ($output_folder && preg_match('!http://www\.britishpathe\.com/media/Reference/00000000/\d+/(\d+)\.jpg!', $flat, $matches)){
      $media_id = $matches[1];
      $image_url = sprintf('http://www.britishpathe.com/media/Reference/00000000/%08d/%08d.jpg', floor($media_id/1000) * 1000, $media_id);
      $video_url = sprintf('rtmp://streaming.britishpathe.com/vod/_definst_/flv:FLASH/00000000/%08d/%08d', floor($media_id/1000) * 1000, $media_id);
      
      debug($image_url);
      file_put_contents(sprintf('%s/%d.jpg', $output_folder, $id), file_get_contents($image_url));
      
      debug($video_url);
      $command = sprintf('flvstreamer --rtmp %s > %s', escapeshellarg($video_url), escapeshellarg(sprintf('%s/%d.flv', $output_folder, $id)));
      debug($command);
      system($command);
    }

    sleep(1);
  } while ($id++ < 100000);
  
  return $items;
}
