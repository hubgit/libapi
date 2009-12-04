<?php

# http://www.ncbi.nlm.nih.gov/entrez/query/static/eutils_help.html

function content_pubmed($q){
  if (!$term = $q['term'])
    return FALSE;
    
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
  
  if (isset($q['from']))
    $from = $q['from'];
  else if ($output_folder && file_exists($output_folder . '/latest'))
    $from = file_get_contents($output_folder . '/latest');
  else
    $from = 0;

  $to = date('Y/m/d', time() + 60*60*24*365*10); // 10 years in future

  $n = 500;

  $items = array();

  foreach (array('edat', 'mdat') as $datetype){ // edat = date added to entrez (pdat = published date), mdat = date modified
    $start = 0;

    $pubmed = new PubMed();

    $params = array(
      'mindate' => $from,
      'maxdate' => $to,
      'datetype' => $datetype,
      );

    $result = $pubmed->search($term, $params);
    if (!$result)
      return FALSE;

    do {
      $params = array(
        'retmax' => $n,
        'retstart' => $start,
        //'sort' => 'pub+date',
        );
     
      $xml = $pubmed->fetch(NULL, $params);
    
      if (!is_object($xml))
        return FALSE;
      
      //debug($xml); exit();
    
      foreach ($xml->PubmedArticle as $article){
        $id = (int) $article->MedlineCitation->PMID;
        $status = (string) $article->MedlineCitation['Status'];
        if ($status == 'In-Data-Review') // FIXME
          continue;
          
        if ($output_folder){
          $out = sprintf('%s/%s.xml', $output_folder, $id);
          file_put_contents($out, $article->asXML());
        }
        else
          $items[$id] = $article;
      }
  
      sleep(1);
    
      $start += $n;
    
    } while ($start < $pubmed->count);
  }

  file_put_contents($output_folder . '/latest', date('Y/m/d'));
  
  return $items;
}
