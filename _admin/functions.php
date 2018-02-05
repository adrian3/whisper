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

function listFolderFiles($dir){
  global $downloadedFolders;
  global $downloadedFiles;
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
        if ($ff!=="_admin"&&$ff!=="_templates"&&$ff!=="_dropbox"&&$ff!=="drafts") {
          listFolderFiles($dir.'/'.$ff);
          array_push($downloadedFolders,$dir.'/'.$ff);
        }
      }
    }
}

function listFF($dir){
  global $dropboxFolders;
  global $dropboxFiles;
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
      if(is_file($dir.'/'.$ff)) {
        if ($ff!==".DS_Store") {
          if (!in_array($dropboxFiles,$dir.'/'.$ff)) {
            array_push($dropboxFiles,$dir.'/'.$ff);
          }
        }
      }
      else if(is_dir($dir.'/'.$ff)) {
        // just to be safe, make sure the folders wouldn't overwrite the important admin folders later on
        if ($ff!=="_admin"&&$ff!=="_templates"&&$ff!=="_dropbox"&&$ff!=="drafts") {
          listFF($dir.'/'.$ff);
          if (!in_array($dropboxFolders,$dir.'/'.$ff)) {
            array_push($dropboxFolders,$dir.'/'.$ff);
          }
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

function getPreviousNextPosts($postTitle,$postList) {
  global $siteUrl;
  $postList = array_reverse($postList);
  for ($i=0; $i < count($postList); $i++) {
    $arrayIndex = $i;
    if ($postList[$i]->title==$postTitle) {
      $arrayIndex = $i;
      $previousPostHTML = "";
      $nextPostHTML = "";
      $prevPostIndex = $i-1;
      if ($prevPostIndex>=0) {
        $previousPostHTML = '<p>Previous: <a href="'.$siteUrl.''.$postList[$prevPostIndex]->fileName.'">'.$postList[$prevPostIndex]->title.'</a></p>';
      }
      $nextPostIndex = $i+1;
      if ($nextPostIndex<count($postList)) {
        $nextPostHTML = '<p>Next: <a href="'.$siteUrl.''.$postList[$nextPostIndex]->fileName.'">'.$postList[$nextPostIndex]->title.'</a></p>';
      }
    }
  }
  return('<hr style="margin: 70px 0;">'.$previousPostHTML . $nextPostHTML);
}

function generateBlogData($dropboxFiles){
  global $prefix;
  global $fullFileList;
  $allCategories = array();
  $writingList = array();
  $dbF = array_reverse($dropboxFiles);

  for ($i=0; $i < count($dbF); $i++) {
    $content = file_get_contents($dbF[$i]);
    $yaml = getBetween($content,"<!---","--->");
    if ($yaml) {
      $file = '/'.cleanFileName($dbF[$i]);
      $title = getBetween($yaml,"title: ","\n");
      $description = getBetween($yaml,"description: ","\n");
      $published = getBetween($yaml,"published: ","\n");
      $publishedDate = getBetween($yaml,"date: ","\n");
      $date=date_create($publishedDate);
      $publishedDate = date_format($date,"Y-m-d\TH:i:sP");

      $postCategories = array();
      $c = getBetween($yaml,"categories: ","\n");
        $cagegory = explode(",",$c);
        for ($x=0; $x < count($cagegory); $x++) {
          if (trim($cagegory[$x])!=="") {
            array_push($postCategories,trim($cagegory[$x]));
            if (!in_array(trim($cagegory[$x]), $allCategories)&&trim($cagegory[$x])!=="") {
              array_push($allCategories,trim($cagegory[$x]));
            }
          }
        }

        if ($published!=="false") {
      $dropboxPath = str_replace($prefix."_dropbox",'',$dbF[$i]);
      $item=array(
        "title"=>$title,
        "fileName"=>str_replace('.md','.html',$file),
        "dropboxFileName"=>$dropboxPath,
        "categories"=>$postCategories,
        "date_published"=>$publishedDate
      );
      array_push($writingList,$item);
      array_push($fullFileList,renameMD($file));
      }
    }
  }

  $postInfo = '{"categories":';
  $categoryJSON = json_encode($allCategories, JSON_PRETTY_PRINT);
  $postInfo .= $categoryJSON;

  $postInfo .= ',"posts":';
  $myJSON = json_encode($writingList, JSON_PRETTY_PRINT);
  $postInfo .= $myJSON;
  $postInfo .= '}';
  // echo $postInfo;

  $myfile = fopen($prefix."posts.json", "w") or die("Unable to open file!");
  fwrite($myfile, $postInfo);
  fclose($myfile);
  return $postInfo;
}

function generatePageData($dropboxFiles) {
  global $prefix;
  global $fullFileList;
  global $blogDirectory;
  $pages = array();

  listFF($prefix."_dropbox");
  for ($i=0; $i < count($dropboxFiles); $i++) {
    if (strpos($dropboxFiles[$i], $blogDirectory."/") !== false) {
      // skip blog posts (anthing with "blog/" in it's path)
    }
    else if (strpos($dropboxFiles[$i],'.jpg') !== false||strpos($dropboxFiles[$i],'.png') !== false||strpos($dropboxFiles[$i],'.gif') !== false) {
      // skip images
    }
    else {
      $originalFile = $dropboxFiles[$i];
      $newFileMD = cleanFileName($dropboxFiles[$i]);
      $newFileHTML = renameMD($newFileMD);
      $pageEditDate = date("F d Y H:i:s",filemtime($dropboxFiles[$i]));

      $content = file_get_contents($dropboxFiles[$i]);
      $yaml = getBetween($content,"<!---","--->");
      $title = getBetween($yaml,"title: ","\n");
      $publishedDate = getBetween($yaml,"date: ","\n");
      $published = getBetween($yaml,"published: ","\n");

      if ($published!=="false") {
        $page=array(
          "title"=>$title,
          "fileName"=>$newFileHTML,
          "dropboxFileName"=>$originalFile,
          "pageEditDate"=>$pageEditDate
        );

        array_push($pages,$page);
        array_push($fullFileList,$newFileHTML);
      }
    }
  }
  $pageInfo = '{"pages":';
  $myJSON = json_encode($pages, JSON_PRETTY_PRINT);
  $pageInfo .= $myJSON;
  $pageInfo .= '}';
  // echo $pageInfo;

  $myfile = fopen($prefix."pages.json", "w") or die("Unable to open file!");
  fwrite($myfile, $pageInfo);
  fclose($myfile);
  return $pageInfo;
}

 ?>
