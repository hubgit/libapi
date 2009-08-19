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

  $to = date('Y/m/d', time() + 60*60*24); // tomorrow
   
  $n = 500;
  $start = 0;
  
  $pubmed = new PubMed();
  $items = array();
  
  $params = array(
    'min_date' => $from,
    'max_date' => $to,
    'datetype' => 'mdat',
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
      if ($output_folder){
        $id = (int) $article->MedlineCitation->PMID;
        
        $out = sprintf('%s/%s.xml', $output_folder, $id);
        file_put_contents($out, $article->asXML());
        
        //$date = current($article->xpath("PubmedData/History/PubMedPubDate[@PubStatus='pubmed']"));
        //if ((int) $date->Year && (int) $date->Month && (int) $date->Day)
          //file_put_contents($output_folder . '/latest', sprintf('%d/%d/%d', (int) $date->Year, (int) $date->Month, (int) $date->Day));
      }
      else
        $items[] = $article;
    }
  
    sleep(1);
    
    $start += $n;
    
  } while ($start < $pubmed->count);

  file_put_contents($output_folder . '/latest', date('Y/m/d'));
  
  return $items;
}
