<?php
require_once("spyc.php");
require_once("lib.php");
require_once("tag_domain.php");

function getDefinedArticleTagList($config): DefinedArticleTagList {
  $tags = spyc_load_file('../' . $config["input"] . '/tags.yaml');
  $list = [];
  foreach($tags as $tag) {
    $list[] = new ArticleTag(
      new ArticleTagSlang($tag['tag_slang']), 
      new ArticleTagDisplayName($tag['tag_display_name'])
    );
  }
  return new DefinedArticleTagList($list);
}