<?php
require_once("lib.php");
require_once("tag_domain.php");
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
  
class ArticleSummary {
  public $title;
  public $date;
  public $tagList;
  function __construct(
    ArticleTitle $title,
    ArticleDate $date,
    ArticleTagList $tagList
  ) {
    $this->title = $title;
    $this->date = $date;
    $this->tagList = $tagList;
  }

  public function hasTag(string $tagName): bool {
    return $this->tagList->hasTag($tagName);
  }

  function toMap(): array {
    $map = [];
    $map['title'] = $this->title->value;
    $map['date'] = $this->date->value;
    $map['tag_slang_list'] = $this->tagList->toSlangArray();
    return $map;
  }

  static function createFromText($text, DefinedArticleTagList $definedArticleTagList): ArticleSummary {
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
        if($definedArticleTagList->hasTag($tag)) {
          $list[] = $definedArticleTagList->getTag($tag);
        } else {
          $list[] = new ArticleTag(new ArticleTagSlang($tag), new ArticleTagDisplayName($tag));
        }
        
      }
    }
    $tagList = new ArticleTagList($list);
    return new ArticleSummary(
      $title,
      $date,
      $tagList
    );
  }

  static function compare(ArticleSummary $a, $b): int {
    return $a->date->timestamp - $b->date->timestamp;
  }

  static function compareInv(ArticleSummary $a, $b): int {
    return -ArticleSummary::compare($a, $b);
  }
}