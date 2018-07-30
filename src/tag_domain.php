<?php
require_once("lib.php");

class ArticleTagSlang extends StringVO {}
class ArticleTagDisplayName extends StringVO {}
class ArticleTag {
  public $slang;
  public $displayName;
  function __construct(
    ArticleTagSlang $slang, 
    ArticleTagDisplayName $displayName
  ) {
    $this->slang = $slang;
    $this->displayName = $displayName;
  }

  function toMap() {
    $map = [];
    $map['tag_slang'] = $this->slang->value;
    $map['tag_display_name'] = $this->displayName->value;
    return $map;
  }
}
class ArticleTagList {
  public $list;
  private $slangMap;
  private $displayNameMap;
  function __construct(array $list) {
    $this->list = $list;

    $this->slangMap = [];
    $this->displayNameMap = [];
    foreach($list as $tag) {
      $this->slangMap[$tag->slang->value] = true;
      $this->displayNameMap[$tag->displayName->value] = true;
    }
  }
  
  public function hasTag(string $tagName): bool {
    return isset($this->slangMap[$tagName]) || isset($this->displayNameMap[$tagName]);
  }

  public function getTag(string $tagName) {
    foreach($this->list as $tag) {
      if($tagName == $tag->slang->value || $tagName == $tag->displayName->value) {
        return $tag;
      }
    }
    return false;
  }

  public function mergeSlang($map): array {
    foreach($this->slangMap as $key => $value) {
      $map[$key] = $value;
    }
    return $map;
  }

  public function toSlangArray(): array {
    $list = [];
    foreach($this->list as $tag) {
      $list[] = $tag->slang->value;
    }
    return $list;
  }

  public function toArray(): array {
    $list = [];
    foreach($this->list as $tag) {
      $list[] = $tag->toMap();
    }
    return $list;
  }
}

class DefinedArticleTagList extends ArticleTagList {}
  

