<?php
header("Content-type: text/plain; charset=utf-8");

require_once("spyc.php");
require_once("lib.php");
require_once("site.php");
require_once("article.php");

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

$list = fileList('../' . $config["input"] . '/posts');
$articleList = [];
foreach($list as $path) {
  $text = file_get_contents($path);
  $articleList[] = ArticleSummary::createFromText($text);
}
/*
function($a, $b) {
  // 日付の降順にならべる
  return $b->date->timestamp - $a->date->timestamp;
}*/
usort($articleList, function($a, $b) { return ArticleSummary::compareInv($a, $b); });

foreach($articleList as $article) {
  var_dump($article->hasTag(new ArticleTagName("sample")));
}

function saveList($site, $list, $filepath) {
  $map = [];
  $map['site'] = $site->toMap();
  $map['list'] = $list;
  var_dump($filepath);
  var_dump(json_encode($map, JSON_PRETTY_PRINT));
}

// allの作成
$page = -1;
$list = [];
$map = [];
foreach($articleList as $i => $article) {
  if($page != $site->numberOfArticleByPage->getPage($i)) {// ページが変わった
    var_dump("change");
    if($page >= 0) {
      // 保存
      saveList($site, $list, 'list/all/' . $page . '.json');
    }
    $page = $site->numberOfArticleByPage->getPage($i);
    $list = [];
  }
  $list[] = $article->toMap();
}
saveList($site, $list, 'list/all/' . $page . '.json');