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
$downloadedFiles = array();
$downloadedFolders = array();
$log_directory = '../_dropbox';
$localFileList = array();
if (is_dir($log_directory)) {
  if ($handle = opendir($log_directory)) {
    while(($file = readdir($handle)) !== FALSE) {
      $localFileList[] = $file;
    }
    closedir($handle);
  }
}

foreach($localFileList as $url) {
    $path = parse_url($url, PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($path!=="."&&$path!==".."&&$path!==".DS_Store") {
      if (strpos($path, ".") !== false) {
        $rev = explode("_rev",$path);
        $cleanPath = $rev[0].".".$ext;
        $rev = explode('.'.$ext,$rev[1]);
        $revID = $rev[0];
        $a=array("path"=>$cleanPath,"rev"=>$revID);
        array_push($downloadedFiles,$a);
      }
      else {
        array_push($downloadedFolders,$path);
      }
    }
}

// Now loop through local folders
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

    // create folders
    // echo $fileName;
    if(!file_exists(dirname($prefix."_dropbox/".$fileName))) {
      mkdir(dirname($prefix."_dropbox/".$fileName), 0777, true);
    }

    // filename comes in like this: index.md_revf77747670
    $fileNameParts = explode("_rev",$fileName);
    $cleanFileName = $fileNameParts[0];
    $fileRev = $fileNameParts[1];
    $f = explode(".",$cleanFileName);
    $ext = $f[1];
    $file = $f[0];

    $fullPath = $prefix ."_dropbox/".$file ."_rev".$fileRev.".".$ext;

    // echo "<br>Downloading: ";
    // echo $fullPath;

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

    // echo "<br>Deleting: ";
    // echo $cleanFileName;
  }
}

RemoveEmptySubFolders($prefix."_dropbox/");
require_once 'rss.php'; // generate rss feeds:

$pageInfo = '{"downloaded":';
  // foreach($dropboxFilesToDownload as $fileName) { echo $fileName;}

$pageInfo .= count($dropboxFilesToDownload);
// $myJSON = json_encode($dropboxFilesToDownload);
// $pageInfo .= $myJSON;
$pageInfo .= ',';
$pageInfo .= '"deleted":';
$pageInfo .= count($downloadedFilesToDelete);
// $myJSON = json_encode($downloadedFilesToDelete);
// $pageInfo .= $myJSON;
$pageInfo .= '}';
echo $pageInfo;

 ?>
