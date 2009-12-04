<?php

class PubMed extends API {
  public $doc = 'http://www.ncbi.nlm.nih.gov/entrez/query/static/eutils_help.html';
  
  public $count;
  public $webenv;
  public $querykey;
  
  function search($q, $params = array()){
    unset($this->count, $this->webenv, $this->querykey);

    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'usehistory' => 'y',
      'retmax' => 1,
      'term' => $q,
      );

    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array_merge($default, $params), 'xml');

    debug($xml);
    if (!is_object($xml))
      return false;

    $this->count = (int) $xml->Count;
    $this->webenv = (string) $xml->WebEnv;
    $this->querykey = (int) $xml->QueryKey;

    return $xml;
  }

  function fetch($ids = NULL, $params = array()){
    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      );
      
    if (!empty($ids)){
      $default['id'] = implode(',', is_array($ids) ? $ids : array($ids));
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }

    return $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array_merge($default, $params), 'xml');
  }

  function content($q){
    if (!$term = $q['term'])
      return FALSE;
    
    if (isset($q['output']))
      $output_dir = output_dir($q['output']);
  
    if (isset($q['from']))
      $from = $q['from'];
    else if ($output_dir && file_exists($output_dir . '/latest'))
      $from = file_get_contents($output_dir . '/latest');
    else
      $from = 0;

    $to = date('Y/m/d', time() + 60*60*24*365*10); // 10 years in future

    $n = 500;

    $items = array();

    foreach (array('edat', 'mdat') as $datetype){ // edat = date added to entrez (pdat = published date), mdat = date modified
      $start = 0;

      $params = array(
        'mindate' => $from,
        'maxdate' => $to,
        'datetype' => $datetype,
        );

      $result = $this->search($term, $params);
      if (!$result)
        return FALSE;

      do {
        $params = array(
          'retmax' => $n,
          'retstart' => $start,
          //'sort' => 'pub+date',
          );
     
        $xml = $this->fetch(NULL, $params);
    
        if (!is_object($xml))
          return FALSE;
      
        //debug($xml);
    
        foreach ($xml->PubmedArticle as $article){
          $id = (int) $article->MedlineCitation->PMID;
          $status = (string) $article->MedlineCitation['Status'];
          if ($status == 'In-Data-Review') // FIXME
            continue;
          
          if ($output_dir){
            $out = sprintf('%s/%d.xml', $output_dir, $id); // id = integer
            file_put_contents($out, $article->asXML());
          }
          else
            $items[$id] = $article;
        }
  
        sleep(1);
    
        $start += $n;
      } while ($start < $pubmed->count);
    }

    file_put_contents($output_dir . '/latest', date('Y/m/d'));
  
    return $items;
  }
  
  // fetch an individual item from PubMed by DOI or PMID
  // TODO: clean up
  function metadata($q){
    if (!$q['pmid'] && $q['doi']){
      $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array(
        'db' => 'pubmed',
        'retmode' => 'xml',
        'retmax' => 1,
        'usehistory' => 'n',
        'term' => $q['doi'] . '[DOI]',
        ), 'xml');

      debug($xml);

      if ((int) $xml->Count > 0)  
        $q['pmid'] = (int) $xml->IdList->Id[0];
    }

    if (!$pmid = $q['pmid'])
      return FALSE;

    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'id' => $pmid,
      ), 'xml');

    //debug($xml);

    if (!is_object($xml))
      return FALSE;

    $article = $xml->PubmedArticle[0]->MedlineCitation->Article;

    $doi = (string) current($xml->xpath("//ArticleIdList/ArticleId[@IdType='doi']"));
    $pmid = (int) current($xml->xpath("//ArticleIdList/ArticleId[@IdType='pubmed']"));

    $authors = array();
    foreach ($article->AuthorList->Author as $author)
      $authors[] = implode(' ', array((string) $author->Initials, (string) $author->LastName));

    return array(
      'pmid' => xpath_item($article, "//ArticleIdList/ArticleId[@IdType='pubmed']"),
      'title' => xpath_item($article, "ArticleTitle"),
      'journal' => xpath_item($article, "Journal/Title"),
      'year' => xpath_item($article, "Journal/JournalIssue/PubDate/Year"),
      'abstract' => xpath_item($article, "Abstract/AbstractText"),
      'doi' => xpath_item($article, "//ArticleIdList/ArticleId[@IdType='doi']"),
      'authors' => $authors,
      'raw' => $article,
      );
  }
}
