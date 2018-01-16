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
require_once 'vendor/autoload.php';
require_once 'processor.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

$app = new DropboxApp($dropboxKey, $dropboxSecret, $dropboxAccessToken);
$dropbox = new Dropbox($app);
$dropboxFiles = array();

function buildDropboxFileTree($folder) {
  global $dropboxFiles;
  global $dropbox;
  $listFolderContents = $dropbox->listFolder($folder);
  $items = $listFolderContents->getItems();

  for ($i=0; $i < count($items); $i++) {
    // if it is a file it has a dot in it, otherwise it is a folder
    if (strpos($items[$i]->name, ".") !== false) {
      $uri = ltrim($items[$i]->getPathDisplay(), '/');
      $a=array("path"=>$uri,"rev"=>$items[$i]->getRev());
      array_push($dropboxFiles,$a);
    }
    else {
      if (strpos(strtolower($items[$i]->getPathDisplay()), "drafts") !== false) {
        // if the path has "drafts" in it, ignore
      }
      else {
        buildDropboxFileTree($items[$i]->getPathDisplay()."/");
      }
    }
  }
}
buildDropboxFileTree("/");

// Create Array of Items in Local Folder
$downloadedFolders = array();
$downloadedFiles = array();

listFolderFiles($prefix."/_dropbox");

// echo "Dropbox Files: ";
// echo "<br>";
// for ($i=0; $i < count($dropboxFiles); $i++) {
//   echo $dropboxFiles[$i][path]." Rev: ".$dropboxFiles[$i][rev]."<br>";
// }
// echo "<br>";
// echo "<br>";
// echo "Local Files: ";
// echo "<br>";
// for ($i=0; $i < count($downloadedFiles); $i++) {
//   echo $downloadedFiles[$i][path]." Rev: ".$downloadedFiles[$i][rev]."<br>";
// }
// echo "<br>";
// echo "<br>";

if (!$dropboxFiles) {
  $dropboxFilesCombined=array("path"=>"","rev"=>"");
}
else {
  foreach($dropboxFiles as $aV){
      $dropboxFilesCombined[] = $aV[path]."_rev".$aV[rev];
  }
}

if (!$downloadedFiles) {
  $downloadedFilesCombined=array("path"=>"na","rev"=>"na");
}
else {
  foreach($downloadedFiles as $aV){
      $downloadedFilesCombined[] = $aV[path]."_rev".$aV[rev];
  }
}

$dropboxFilesToDownload = array_diff($dropboxFilesCombined,$downloadedFilesCombined);

if ($dropboxFilesToDownload) {
  foreach($dropboxFilesToDownload as $fileName) {
    if(!file_exists(dirname($prefix."_dropbox/".$fileName))) {
      mkdir(dirname($prefix."_dropbox/".$fileName), 0777, true);
    }
    $fileNameParts = explode("_rev",$fileName);
    $cleanFileName = $fileNameParts[0];
    $fileRev = $fileNameParts[1];
    $f = explode(".",$cleanFileName);
    $ext = $f[1];
    $file = $f[0];

    $fullPath = $prefix ."_dropbox/".$file ."_rev".$fileRev.".".$ext;
    $dropbox->download("/".$cleanFileName, $fullPath);
    $f = str_replace($prefix."_dropbox/","",$fullPath);
    processFile($f);
  }
}

$downloadedFilesToDelete = array_diff($downloadedFilesCombined,$dropboxFilesCombined);

if ($downloadedFilesToDelete) {
  foreach($downloadedFilesToDelete as $fileName) {
    $f = explode(".",$fileName);
    $pre = $f[0];
    $post = $f[1];
    $e = explode("_rev",$post);
    $ext = $e[0];
    $rev = $e[1];
    $cleanFileName = $pre ."_rev".$rev.".".$ext;
    unlink($prefix."_dropbox/".$cleanFileName);
  }
}

RemoveEmptySubFolders($prefix."_dropbox/");
require_once 'rss.php'; // generate rss feeds:

$pageInfo = '{"downloaded":';
$pageInfo .= count($dropboxFilesToDownload);
$pageInfo .= ',';
$pageInfo .= '"deleted":';
$pageInfo .= count($downloadedFilesToDelete);
$pageInfo .= '}';
echo $pageInfo;

 ?>
