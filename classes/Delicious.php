<?php

class Delicious extends API { 
  public $doc = 'http://delicious.com/';
  //public $def = 'DELICIOUS_AUTH'; // only for fetching a user's content
  
  public $tags = array();
   
  function get_bookmarks_for_item($uri){
    $this->get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));

    $this->total = $this->data[0]->total_posts;
    $this->tags = $this->data[0]->top_tags;

    $this->get_data('http://feeds.delicious.com/v2/rss/url/' . md5($uri), array(), 'dom');
    $this->xpath->registerNamespace('dcel', 'http://purl.org/dc/elements/1.1/');

    foreach ($this->xpath->query('channel/item') as $node)
      $items[] = array(
        'user' => $this->xpath->query("dcel:creator", $node)->item(0)->nodeValue,
        'date' => $this->xpath->query("pubDate", $node)->item(0)->nodeValue,
        'title' => $this->xpath->query("title", $node)->item(0)->nodeValue,
        'tags' => $this->xpath->query("category", $node)->item(0)->nodeValue,
        );
  }

  # http://delicious.com/tag
  // TODO: is there a limit on the API?
  function content_by_tag($tag, $max = 1000000){
    require_once 'lib/css2xpath.inc.php';
          
    if ($this->output_dir)
      $this->output_dir = $this->get_output_dir($this->output_dir . '/' . preg_replace('/\W/', '_', $tag)); // FIXME: proper sanitising

    $css2xpath = new CSS2XPath();

    $xpath = array_map(array($css2xpath, 'transform'), array(
      'item' => '#bookmarklist .post .bookmark',
      'link' => 'a.taggedlink',
      'user' => 'a.user span',
      'next' => '#pagination .next',
      'count' => '.delNavCount',
      'tag' => 'a[rel=tag] span',
      'date' => '.dateGroup span',
      ));

    $page = 1;
    $n = 100;

    $count = 0;
    do{
      $this->get_data('http://delicious.com/tag/' . urlencode($tag), array(
        'setcount' => $n,
        'page' => $page,
        ), 'html', $http);

      foreach ($this->data->xpath($xpath['item']) as $item){
        $link = current($item->xpath($xpath['link']));

        $result = $item->xpath($xpath['date']);
        if (!empty($result))
          $date = strtotime((string) $result[0]['title']);
        if (!$date)
          break(2);

        $data = array(
          'uri' => (string) $link['href'],
          'title' => (string) $link,
          'user' => (string) current($item->xpath($xpath['user'])),
          'count' => (int) current($item->xpath($xpath['count'])),
          'date' => $date,
        );

        if (!$data['uri'])
          break(2);

        $tags = array();
        foreach ($item->xpath($xpath['tag']) as $tag)
          $data['tags'][] = (string) $tag;

        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.js', $this->output_dir, base64_encode_file($data['user'] .  '*' . $data['uri'])), json_encode($data)); 
        else
          $this->results[] = $item;
      }

      $next = current($xml->xpath($xpath['next']));
      $page = 0;
      if (preg_match('/page=(\d+)$/', (string) $next['href'], $matches)){
        $page = $matches[1];
        sleep(1);
      }

      debug('Page ' . $page);
    } while ($page);
  }
  
  function content_by_user($user, $from){
    $from = $this->get_latest($from, 0); // 0 = 1970-01-01T00:00:00Z

    $http = array('header' => sprintf('Authorization: Basic %s', base64_encode(Config::get('DELICIOUS_AUTH'))));    
    $this->get_data('https://api.del.icio.us/v1/posts/all', array('fromdt' => gmdate("Y-m-d\TH:i:s\Z", $from)), 'xml', $http);

    foreach ($this->data->post as $item){
      if ($this->output_dir)
        file_put_contents(sprintf('%s/%s.xml', $this->output_dir, preg_replace('/\W/i', '', (string) $item['hash'])), $item->asXML()); 
      else
        $this->results[] = $item;
    }

    if ($this->output_dir)
      file_put_contents($this->output_dir . '/latest', strtotime((string) $this->data['update']));
  }
}
