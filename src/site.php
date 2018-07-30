<?php
require_once("lib.php");

class SiteTitle extends StringVo {}
class SiteDescription extends StringVo {}
class SiteLink extends StringVo {}
class SiteAuthor extends StringVo {}
class NumberOfArticleByPage extends IntVo {
  public function getPage(int $articleIndex): Int {
    return $articleIndex / $this->value;
  }
}
class Site {
  public $title;
  public $description;
  public $link;
  public $author;
  public $numberOfArticleByPage;
  function __construct(
    SiteTitle $title,
    SiteDescription $description,
    SiteLink $link,
    SiteAuthor $author,
    NumberOfArticleByPage $numberOfArticleByPage
  ) {
    $this->title = $title;
    $this->description = $description;
    $this->link = $link;
    $this->author = $author;
    $this->numberOfArticleByPage = $numberOfArticleByPage;
  }

  function toMap(): array {
    $map = [];
    $map['site_title'] = $this->title->value;
    $map['site_description'] = $this->description->value;
    $map['site_link'] = $this->link->value;
    $map['site_author'] = $this->author->value;
    $map['num_article_by_page'] = $this->numberOfArticleByPage->value;
    return $map;
  }

  static function createFromYamlMap($map): Site {
    return new Site(
      new SiteTitle($map['site_title']),
      new SiteDescription($map['site_description']),
      new SiteLink($map['site_link']),
      new SiteAuthor($map['site_author']),
      new NumberOfArticleByPage(isset($map['num_article_by_page']) ? $map['num_article_by_page'] : 5)
    );
  }
}