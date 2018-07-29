<?php
require_once("lib.php");
class ArticleTitle extends StringVO {}
  class ArticleDate {
    public $value;
    public $timestamp;
    function __construct($value) {
      $this->value = $value;
      $this->timestamp = strtotime($value);
    }
    function __get($name){
      if($name == 'value') {
        return $this->value;
      }
    }
  }
  class ArticleTagName extends StringVO {}
  class ArticleTagNameList {
    public $list;
    private $map;
    function __construct(array $list) {
      $this->list = $list;
  
      $this->map = [];
      foreach($list as $tagName) {
        $this->map[$tagName->value] = true;
      }
    }
    
    public function hasTag(ArticleTagName $tagName): bool {
      return isset($this->map[$tagName->value]);
    }
  }
  
  class ArticleSummary {
    public $title;
    public $date;
    public $tagNameList;
    function __construct(
      ArticleTitle $title,
      ArticleDate $date,
      ArticleTagNameList $tagNameList
    ) {
      $this->title = $title;
      $this->date = $date;
      $this->tagNameList = $tagNameList;
    }
  
    public function hasTag(ArticleTagName $tagName): bool {
      return $this->tagNameList->hasTag($tagName);
    }

    function toMap(): array {
      $map = [];
      $map['title'] = $this->title->value;
      $map['date'] = $this->date->value;
      return $map;
    }
  
    static function createFromText($text): ArticleSummary {
      // var_dump($text);
      $first = strpos($text, '---');
      $second = strpos($text, '---', $first + 3);
      $r = trim(substr($text, 3, $second - 3));
      $map = spyc_load($r);
      $title = new ArticleTitle($map['title']);
      $date = new ArticleDate($map['date']);
      $list = [];
      if(isset($map['tags'])) {
        foreach($map['tags'] as $tag) {
          $list[] = new ArticleTagName($tag);
        }
      }
      $tagNameList = new ArticleTagNameList($list);
      return new ArticleSummary(
        $title,
        $date,
        $tagNameList
      );
    }
  
    static function compare(ArticleSummary $a, $b): int {
      return $a->date->timestamp - $b->date->timestamp;
    }
  
    static function compareInv(ArticleSummary $a, $b): int {
      return -ArticleSummary::compare($a, $b);
    }
  }