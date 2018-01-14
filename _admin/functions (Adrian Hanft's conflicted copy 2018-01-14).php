<?php
function getBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

function cleanFileName($path){
  $ext = pathinfo($path, PATHINFO_EXTENSION);
  $rev = explode("_rev",$path);
  $rev = explode('.'.$ext,$rev[1]);
  $revID = $rev[0];
  $fileName = explode("/_dropbox/",$path);
  $fileName = $fileName[1];
  $pref = explode("_rev",$fileName);
  $pref = $pref[0];
  $cleanPath = $pref.".".$ext;
  return($cleanPath);
}

$dropboxFolders = array();
$dropboxFiles = array();

function listFolderFiles($dir, &$downloadedFolders, &$downloadedFiles){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
      if(is_file($dir.'/'.$ff)) {
        if ($ff!==".DS_Store"&&$ff!==".config.codekit3") {

          $path = $dir.'/'.$ff;
          $ext = pathinfo($path, PATHINFO_EXTENSION);
          $rev = explode("_rev",$path);
          $rev = explode('.'.$ext,$rev[1]);
          $revID = $rev[0];
          $fileName = explode("/_dropbox/",$path);
          $fileName = $fileName[1];
          $pref = explode("_rev",$fileName);
          $pref = $pref[0];
          $cleanPath = $pref.".".$ext;
          $a=array("path"=>$cleanPath,"rev"=>$revID);
          array_push($downloadedFiles,$a);
        }
      }
      else if(is_dir($dir.'/'.$ff)) {
        // just to be safe, make sure the folders wouldn't overwrite the important admin folders later on
        if ($ff!=="_admin"&&$ff!=="_templates"&&$ff!=="_dropbox"&&$ff!=="images") {
          listFolderFiles($dir.'/'.$ff,$downloadedFolders, $downloadedFiles);
          array_push($downloadedFolders,$dir.'/'.$ff);
        }
      }
    }
}

function listFF($dir, &$dropboxFolders, &$dropboxFiles){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
      if(is_file($dir.'/'.$ff)) {
        if ($ff!==".DS_Store") {
          array_push($dropboxFiles,$dir.'/'.$ff);
        }
      }
      else if(is_dir($dir.'/'.$ff)) {
        // just to be safe, make sure the folders wouldn't overwrite the important admin folders later on
        if ($ff!=="_admin"&&$ff!=="_templates"&&$ff!=="_dropbox") {
          listFF($dir.'/'.$ff,$dropboxFolders, $dropboxFiles);
          array_push($dropboxFolders,$dir.'/'.$ff);
        }
      }
    }
    return($dropboxFiles);
}


function stripRev($path){
  $ext = getExtension($path);
  $rev = explode("_rev",$path);
  $fileName = $rev[0].".".$ext;
  return($fileName);
}

function renameMD($mdName){
  return(str_replace('.md','.html',$mdName));
}

function RemoveEmptySubFolders($path) {
  $empty=true;
  foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
     if (is_dir($file)) {
        if (!RemoveEmptySubFolders($file)) $empty=false;
     }
     else {
        $empty=false;
     }
  }
  if ($empty) rmdir($path);
  return $empty;
}

 ?>
