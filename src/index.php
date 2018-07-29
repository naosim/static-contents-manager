<?php
include("spyc.php");
require_once("lib.php");
$text = "
title: フガー
date: 2018-07-27 12:00:00
tags:
  - sample
  - foo
";
$config = spyc_load_file("../config.yaml");
$siteYaml = spyc_load_file('../' . $config["input"] . '/site.yaml');

class SiteTitle extends StringVo {}
class SiteDescription extends StringVo {}
class SiteLink extends StringVo {}
class SiteAuthor extends StringVo {}
class Site {
  public $title;
  public $description;
  public $link;
  public $author;
  function __construct(
    SiteTitle $title,
    SiteDescription $description,
    SiteLink $link,
    SiteAuthor $author
  ) {
    $this->title = $title;
    $this->description = $description;
    $this->link = $link;
    $this->author = $author;
  }

  static function createFromYamlMap($map): Site {
    return new Site(
      new SiteTitle($map['site_title']),
      new SiteDescription($map['site_description']),
      new SiteLink($map['site_link']),
      new SiteAuthor($map['site_author'])
    );
  }
}

class ArticleTitle extends StringVO {}
class ArticleDate extends StringVO {
  public $timestamp;
  function __construct($value) {
    $this->value = $value;
    $this->strtotime($value);
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
  function __construct(array $list) {
    $this->list = $list;
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
}

$text = file_get_contents('../inputdata/posts/2018/07/26_sample.md');
var_dump(ArticleSummary::createFromText($text));

$site = Site::createFromYamlMap($siteYaml);
var_dump($site);

function fileList($path) {
  $ary = [];
  foreach(glob($path . '/*') as $file){
    if(is_file($file)){
      $ary[] = $file;
    }
    if(is_dir($file)) {
      foreach(fileList($file) as $f) {
        $ary[] = $f;
      }
    }
  }
  return $ary;
  
}

$list = fileList('../' . $config["input"] . '/posts');
var_dump($list);