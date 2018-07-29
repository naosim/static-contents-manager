<?php
header("Content-type: text/plain; charset=utf-8");

require_once("spyc.php");
require_once("lib.php");
require_once("site.php");
require_once("article_domain.php");
require_once("tag_infra.php");

$config = spyc_load_file("../config.yaml");
$siteYaml = spyc_load_file('../' . $config["input"] . '/site.yaml');

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

function toKeyList($map) {
  $list = [];
  foreach($map as $key => $value) {
    $list[] = $key;
  }
  return $list;
}

system("rm -rf ../tmp");
system("mkdir ../tmp");

$definedArticleTagList = getDefinedArticleTagList($config);

$list = fileList('../' . $config["input"] . '/posts');
$articleList = [];
$tagSlangMap = [];
foreach($list as $path) {
  $text = file_get_contents($path);
  $article = ArticleSummary::createFromText($text, $definedArticleTagList);
  $tagSlangMap = $article->tagList->mergeSlang($tagSlangMap);
  $articleList[] = $article;
}
$tagSlangList = toKeyList($tagSlangMap);
/*
function($a, $b) {
  // 日付の降順にならべる
  return $b->date->timestamp - $a->date->timestamp;
}*/
usort($articleList, function($a, $b) { return ArticleSummary::compareInv($a, $b); });

// foreach($articleList as $article) {
//   var_dump($article->hasTag("sample"));
// }
var_dump($tagSlangList);

function saveList($config, $site, $list, $tagName, $page) {
  $map = [];
  $map['site'] = $site->toMap();
  $map['list'] = $list;
  $path = "../tmp/list/$tagName";
  mkdir("../tmp/list");
  mkdir("../tmp/list/$tagName");
  $filename = "$page.json";
  var_dump("$path/$filename");
  var_dump(json_encode($map, JSON_PRETTY_PRINT));
  
  file_put_contents("$path/$filename", json_encode($map, JSON_PRETTY_PRINT));
}

function saveTags($config, $site, $tagSlangList, $definedArticleTagList) {
  $result = [];
  $result['site'] = $site->toMap();
  $result['tags'] = [];
  foreach($tagSlangList as $tagName) {
    $tag = null;
    if($definedArticleTagList->hasTag($tagName)) {
      $tag = $definedArticleTagList->getTag($tagName);
    } else {
      $tag = new ArticleTag(new ArticleTagSlang($tagName), new ArticleTagDisplayName($tagName));
    }
    $map = $tag->toMap();
    $map['link'] = $site->link->value . "/list/$tagName/0.json";
    $result['tags'][] = $map;
  }
  var_dump($result);
  $path = '../tmp/tags.json';
  var_dump($path);
  file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT));
}

function createList($config, $site, $articleList, $tagName) {
  $list = [];
  $page = 0;
  foreach($articleList as $i => $article) {
    $page = $site->numberOfArticleByPage->getPage($i);
    if(!isset($list[$page])) {
      $list[] = [];
    }
    $list[$page][] = $article->toMap();
  }

  foreach($list as $index => $articleList) {
    saveList($config, $site, $articleList, $tagName, $index);
  }
}

// allの作成
createList($config, $site, $articleList, 'all');

// tag別
foreach($tagSlangList as $tagName) {
  $filteredArticleList = [];
  foreach($articleList as $article) {
    if($article->hasTag($tagName)) {
      $filteredArticleList[] = $article;
    }
  }

  createList($config, $site, $filteredArticleList, $tagName);
}

saveTags($config, $site, $tagSlangList, $definedArticleTagList);