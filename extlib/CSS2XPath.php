<?php

// ported from the javascript version at http://code.google.com/p/css2xpath/

class CSS2XPath{
  function transform($s){
    $i = 0;
    $n = $this->length;
    while ($i < $n){
      $re = $this->re[$i++];
      $replace = $this->re[$i++];
      
      if (preg_match('/^function (.+)/', $replace, $matches))
        $s = preg_replace_callback($re, array($this, $matches[1]), $s);
      else
        $s = preg_replace($re, $replace, $s);
    }
    return 'descendant::' . $s;
  }
  
  function __construct(){
    $this->re = array(
      // add @ for attribs
      '/\[([^\]~\$\*\^\|\!]+)(=[^\]]+)?\]/', '[@$1$2]',
      // multiple queries
      '/\s*,\s*/', '|',
      // , + ~ >
      '/\s*(\+|~|>)\s*/', '$1',
      //* ~ + >
      '/([a-zA-Z0-9\_\-\*])~([a-zA-Z0-9\_\-\*])/', '$1/following-sibling::$2',
      '/([a-zA-Z0-9\_\-\*])\+([a-zA-Z0-9\_\-\*])/', '$1/following-sibling::*[1]/self::$2',
      '/([a-zA-Z0-9\_\-\*])>([a-zA-Z0-9\_\-\*])/', '$1/$2',
      // all unescaped stuff escaped
      '/\[([^=]+)=([^\'|"][^\]]*)\]/', '[$1=\'$2\']',
      // all descendant or self to //
      '/(^|[^a-zA-Z0-9\_\-\*])(#|\.)([a-zA-Z0-9\_\-]+)/', '$1*$2$3',
      '/([\>\+\|\~\,\s])([a-zA-Z\*]+)/', '$1//$2',
      '/\s+\/\//', '//',
      // :first-child
      '/([a-zA-Z0-9\_\-\*]+):first-child/', '*[1]/self::$1',
      // :last-child
      '/([a-zA-Z0-9\_\-\*]+):last-child/', '$1[not(following-sibling::*)]',
      // :only-child
      '/([a-zA-Z0-9\_\-\*]+):only-child/', '*[last()=1]/self::$1',
      // :empty
      '/([a-zA-Z0-9\_\-\*]+):empty/', '$1[not(*) and not(normalize-space())]',
      // :not
      '/([a-zA-Z0-9\_\-\*]+):not\(([^\)]*)\)/', 'function not',
      // :nth-child
      '/([a-zA-Z0-9\_\-\*]+):nth-child\(([^\)]*)\)/', 'function nth_child',
      // :contains(selectors)
      '/:contains\(([^\)]*)\)/', 'function contains',
      // |= attrib
      '/\[([a-zA-Z0-9\_\-]+)\|=([^\]]+)\]/', '[@$1=$2 or starts-with(@$1,concat($2,"-"))]',
      // *= attrib
      '/\[([a-zA-Z0-9\_\-]+)\*=([^\]]+)\]/', '[contains(@$1,$2)]',
      // ~= attrib
      '/\[([a-zA-Z0-9\_\-]+)~=([^\]]+)\]/', '[contains(concat(" ",normalize-space(@$1)," "),concat(" ",$2," "))]',
      // ^= attrib
      '/\[([a-zA-Z0-9\_\-]+)\^=([^\]]+)\]/', '[starts-with(@$1,$2)]',
      // $= attrib
      '/\[([a-zA-Z0-9\_\-]+)\$=([^\]]+)\]/', 'function attrib',
      // != attrib
      '/\[([a-zA-Z0-9\_\-]+)\!=([^\]]+)\]/', '[not(@$1) or @$1!=$2]',
      // ids and classes
      '/#([a-zA-Z0-9\_\-]+)/', '[@id="$1"]',
      '/\.([a-zA-Z0-9\_\-]+)/', '[contains(concat(" ",normalize-space(@class)," ")," $1 ")]',
      // normalize multiple filters
      '/\]\[([^\]]+)/', ' and ($1)'
    );
    
    $this->length = count($this->re);
  }
  
  // :not
  function not($s, $a, $b){
    return implode($a, array('[not(', preg_replace('/^[^\[]+\[([^\]]*)\].*$/', '$1', $this->transform($b)), ')]'));   
  }
  
  // $= attrib
  function attrib($s, $a, $b){
    return implode('[substring(@', array($a, ',string-length(@', $a, ')-', strlen($b) - 3, ')=', $b, ']'));
  }
  
  // :nth-child
  function nth_child($s, $a, $b = NULL){ 
    switch($b){
      case 'n':
        return a;
      case 'even':
        return '*[position() mod 2=0 and position()>=0]/self::' . a;
      case 'odd':
        return a . '[(count(preceding-sibling::*) + 1) mod 2=1]';
      default:
        $b = isset($b) ? $b : '0';
        $b = preg_replace('/^([0-9]*)n.*?([0-9]*)$/', '$1+$2', $b);
        $b = explode('+', $b);
        $b[1] = isset($b[1]) ? $b[1] : '0';
        return implode('*[(position()-', array($b[1], ') mod ', $b[0], '=0 and position()>=', $b[1], ']/self::', $a));
    }
  }
  
  // :contains(selectors)
  function contains($s, $a){
    return '[contains(string(.),\'' . $a . '\')]';
  }
}
