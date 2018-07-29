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

function mkdirs($path) {
  if(!is_dir($path)){
    mkdir($path, 0777, true);
  }
}

system("rm -rf ../tmp");
system("mkdir ../tmp");
system("chmod 777 ../tmp");

$definedArticleTagList = getDefinedArticleTagList($config);

$root = '../' . $config["input"] . '/posts';
$list = fileList($root);
$articleList = [];
$tagSlangMap = [];
foreach($list as $path) {
  $articlePath = new ArticlePath($site->link, '/posts' . substr($path, strlen($root)) . '.json');
  $text = file_get_contents($path);
  $article = ArticleSummary::createFromText($text, $definedArticleTagList, $articlePath);
  $tagSlangMap = $article->tagList->mergeSlang($tagSlangMap);
  $articleList[] = $article;
}
$tagSlangList = toKeyList($tagSlangMap);
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
  mkdirs($path);
  $filename = "$page.json";
  var_dump("$path/$filename");
  var_dump(json_encode($map, JSON_PRETTY_PRINT));
  
  file_put_contents("$path/$filename", json_encode($map, JSON_PRETTY_PRINT));
}

function saveSite($config, $site) {
  $path = '../tmp/site.json';
  file_put_contents($path, json_encode($site->toMap(), JSON_PRETTY_PRINT));
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

function saveAllArticle($config, $site, $definedArticleTagList) {
  $root = '../' . $config["input"] . '/posts';
  $list = fileList($root);
  $articleList = [];
  $tagSlangMap = [];
  foreach($list as $path) {
    $articlePath = new ArticlePath($site->link, '/posts' . substr($path, strlen($root)));
    $text = file_get_contents($path);
    $article = ArticleDetail::createFromText($text, $definedArticleTagList, $articlePath);
    // $p = substr($path, strlen($root));
    $p = '../tmp/posts' . substr($path, strlen($root)) . '.json';
    $dir = substr($p, 0, strrpos($p, '/'));
    mkdirs($dir);
    
    $map = [];
    $map['site'] = $site->toMap();
    $map['contents'] = $article->toMap();
    var_dump($map);
    file_put_contents($p, json_encode($map, JSON_PRETTY_PRINT));
  }
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

saveSite($config, $site);
saveTags($config, $site, $tagSlangList, $definedArticleTagList);
saveAllArticle($config, $site, $definedArticleTagList);