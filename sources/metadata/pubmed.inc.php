<?php

# fetch an individual item from PubMed by DOI or PMID

function metadata_pubmed($q){
  if (!$q['pmid'] && $q['doi']){
    $xml = get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array(
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

  $xml = get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array(
    'db' => 'pubmed',
    'retmode' => 'xml',
    'id' => $pmid,
    ), 'xml');
  
  debug($xml);
  
  if (!is_object($xml))
    return FALSE;
  
  $article = $xml->PubmedArticle[0]->MedlineCitation->Article;
  
  $doi = (string) array_shift($xml->xpath("//ArticleIdList/ArticleId[@IdType='doi']"));
  $pmid = (int) array_shift($xml->xpath("//ArticleIdList/ArticleId[@IdType='pubmed']"));

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

