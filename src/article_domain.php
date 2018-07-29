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
class ArticlePath {
  public $value;
  public $path;
  function __construct(
    SiteLink $siteLink,
    string $path
  ) {
    $this->value = $siteLink->value . $path;
    $this->path = $path;
  }
}
  
class ArticleSummary {
  public $title;
  public $date;
  public $tagList;
  public $path;
  function __construct(
    ArticleTitle $title,
    ArticleDate $date,
    ArticleTagList $tagList,
    ArticlePath $path
  ) {
    $this->title = $title;
    $this->date = $date;
    $this->tagList = $tagList;
    $this->path = $path;
  }

  public function hasTag(string $tagName): bool {
    return $this->tagList->hasTag($tagName);
  }

  function toMap(): array {
    $map = [];
    $map['title'] = $this->title->value;
    $map['date'] = $this->date->value;
    $map['tag_slang_list'] = $this->tagList->toSlangArray();
    $map['link'] = $this->path->value;
    return $map;
  }

  static function createFromText($text, DefinedArticleTagList $definedArticleTagList, ArticlePath $path): ArticleSummary {
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
      $tagList,
      $path
    );
  }

  static function compare(ArticleSummary $a, $b): int {
    return $a->date->timestamp - $b->date->timestamp;
  }

  static function compareInv(ArticleSummary $a, $b): int {
    return -ArticleSummary::compare($a, $b);
  }
}

class ArticleBody extends StringVO {}

class ArticleDetail {
  public $title;
  public $date;
  public $tagList;
  public $body;
  function __construct(
    ArticleSummary $summary,
    ArticleBody $body
  ) {
    $this->title = $summary->title;
    $this->date = $summary->date;
    $this->tagList = $summary->tagList;
    $this->body = $body;
  }

  public static function createFromText($text, DefinedArticleTagList $definedArticleTagList, ArticlePath $path): ArticleDetail {
    $first = strpos($text, '---');
    $second = strpos($text, '---', $first + 3);
    $body = new ArticleBody(trim(substr($text, $second + 3)));
    return new ArticleDetail(
      ArticleSummary::createFromText($text, $definedArticleTagList, $path),
      $body
    );
  }

  function toMap() {
    $map = [];
    $map['title'] = $this->title->value;
    $map['date'] = $this->date->value;
    $map['tag_slang_list'] = $this->tagList->toArray();
    $map['body'] = $this->body->value;
    return $map;
  }

}