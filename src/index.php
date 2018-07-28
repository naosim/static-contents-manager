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
class ArticleDate extends StringVO {}
class ArticleTagName extends StringVO {}

class ArticleSummary {

}

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