<?php
require_once "config.php";
// Get the password if called from the cron job:
if (defined('STDIN')) {
  $cronPassword = $argv[1];
  if ($cronPassword !== "password=".$password) { die("Wrong password."); }
} else {
  if ($password !== $_GET['password']) { die("Wrong password."); }
}
require_once 'functions.php';
require_once 'vendor/Parsedown.php';

function processFile($filePath){
  global $prefix;
  global $theme;
  if (getExtension($filePath)=="md") {
    return processMD($filePath);
  }
  else {
    return copyFile($filePath);
  }
}

function processMD($filePath){
  global $prefix;
  global $theme;
  global $siteTitle;
  global $siteUrl;
  global $twitterHandle;
  global $instagramHandle;
  global $copyright;
  global $blogDirectory;

// readme.md files get passed through without processing. Useful for Github hosted sites.
if (strpos($filePath, 'readme') !== false) {
  return copyFile($filePath);
}

  ob_start();
  include $prefix."_themes/".$theme."/header.php";
  $header = ob_get_clean();

  ob_start();
  include $prefix."_themes/".$theme."/footer.php";
  $footer = ob_get_clean();

  $content = file_get_contents($prefix.'_dropbox/'.$filePath);
  $yaml = getBetween($content,"<!---","--->");
  $title = getBetween($yaml,"title: ","\n");
  $categories = getBetween($yaml,"categories: ","\n");
  $description = getBetween($yaml,"description: ","\n");
  $published = getBetween($yaml,"published: ","\n");
  $publishedDate = getBetween($yaml,"date: ","\n");
  $date=date_create($publishedDate);
  $publishedDate = date_format($date,"D, d M y H:i:s O");
  $prettyDate = date_format($date,"F j, Y");

if ($published!=="false") {

    $Parsedown = new Parsedown();
    $html = $Parsedown->text($content);

    // variable replacement: body class
    if (strpos($header, '{{bodyclass}}') !== false) {
      $bodyClass = basename(stripRev($filePath),".md");
      $header = str_replace('{{bodyclass}}',$bodyClass,$header);
    }

    // if the blog directory is in the file path it is a blog post, add previous/next javascript
    if (strpos($filePath, $blogDirectory) !== false) {
      $prevNextJS = file_get_contents($prefix.'_themes/_shared/previousNext.js');
      $footer = str_replace('// {{customJavascript}}',$prevNextJS,$footer);
      $header = str_replace('<div class="postDate" style="display:none;"></div>',$prettyDate,$header);
    }

    $html = $header.$html.$footer;

    $html = customFilter($html);

    // variable replacement: Title
    $html = str_replace('<title></title>','<title>'.$title.'</title>',$html);

    return saveFile(renameMD($filePath), $html);
  }
}

function saveFile($path, $content){
  global $prefix;
  mkdirr(dirname($prefix . $path));
  $destination = $prefix . stripRev($path);
  $myfile = fopen($destination, "w") or die("Unable to open file!");
  fwrite($myfile, $content);
  fclose($myfile);
  if (file_exists($destination)) {
    return '{"success": "'.$path.'"}';
  } else{
    return '{"error": "'.$path.'"}';
  }
}

function copyFile($path){
  global $prefix;
  $source = $prefix."_dropbox/".$path;
  $destination = $prefix . stripRev($path);
  mkdirr(dirname($destination));
  copy($source,$destination);
  return '{"success": "'.$path.'"}';
}

function mkdirr($path) {
  // recursively creates folders from path
  if(is_dir($path)) return true;
  return mkdirr(dirname($path)) && @mkdir($path);
}

function getExtension($filePath) {
  return(pathinfo($filePath, PATHINFO_EXTENSION));
}

if ($_GET['path']) {
  $file = $_GET['path'];
  echo processFile($file);
}

 ?>
