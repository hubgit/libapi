<?php

class BritishPathe extends API {  
  function content($start = 1){        
    $items = array();
    $attempts = array();
    
    $id = $start;
    do{      
      $this->get_data('http://www.britishpathe.com/record.php', array(
        'id' => $id,
        'view' => 'print',
      ), 'html', array('timeout' => 120));
    
      if ($this->http_status == 403)
        break;
        
      if ($this->http_status != 200){
        if (++$attempts[$id] > 2) // tried to get this item twice, so move on
          $id++;
        continue;
      }
    
      if (empty($this->data->body))
        break;
            
      if ($this->output_dir)
        file_put_contents(sprintf('%s/%d.html', $this->output_dir, $id), $this->data->asXML()); 
      else
        $this->results[] = $item;
    
      if ($this->output_dir && preg_match('!http://www\.britishpathe\.com/media/Reference/00000000/\d+/(\d+)\.jpg!', $this->data->asXML(), $matches)){
        $media_id = $matches[1];
        $image_url = sprintf('http://www.britishpathe.com/media/Reference/00000000/%08d/%08d.jpg', floor($media_id/1000) * 1000, $media_id);
        $video_url = sprintf('rtmp://streaming.britishpathe.com/vod/_definst_/flv:FLASH/00000000/%08d/%08d', floor($media_id/1000) * 1000, $media_id);
      
        debug($image_url);
        file_put_contents(sprintf('%s/%d.jpg', $this->output_dir, $id), file_get_contents($image_url));
      
        debug($video_url);
        $command = sprintf('flvstreamer --rtmp %s > %s', escapeshellarg($video_url), escapeshellarg(sprintf('%s/%d.flv', $this->output_dir, $id)));
        debug($command);
        system($command);
      }

      sleep(1);
    } while ($id++ < 100000);
  }
}
