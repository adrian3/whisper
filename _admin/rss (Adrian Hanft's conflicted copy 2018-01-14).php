<?php
require_once 'config.php';
// Get the password if called from the cron job:
if (defined('STDIN')) {
  $cronPassword = $argv[1];
  if ($cronPassword !== "password=".$password) { die("Wrong password."); }
} else {
  if ($password !== $_GET['password']) { die("Wrong password."); }
}
require_once 'functions.php';

function generateRssXml(&$dropboxFiles){
  global $prefix;
  global $siteTitle;
  global $siteUrl;
  $rssFeed = array();

  for ($i=0; $i < count($dropboxFiles); $i++) {
    $content = file_get_contents($dropboxFiles[$i]);
    $yaml = getBetween($content,"<!---","--->");
    $file = cleanFileName($dropboxFiles[$i]);
    $title = getBetween($yaml,"title: ","\n");
    $categories = getBetween($yaml,"categories: ","\n");
    $description = getBetween($yaml,"description: ","\n");
    $published = getBetween($yaml,"published: ","\n");
    $publishedDate = getBetween($yaml,"date: ","\n");
    $date=date_create($publishedDate);
    $publishedDate = date_format($date,"D, d M Y H:i:s O");

    $item=array(
      "link"=>$siteUrl.$blogDirectory.'/'.str_replace(" ","%20",$file),
      "source"=>$siteUrl.$blogDirectory.'/'.$file,
      "description"=>$description,
      "id"=>$file,
      "title"=>$title,
      "pubDate"=>$publishedDate,
      "category"=>$categories
    );
    array_push($rssFeed,$item);
  }

  $rssTitle = $siteTitle.": RSS Feed";
  $rssLink = $siteUrl."/rss.xml";
  $rssDescription = $siteTitle.": RSS Feed";

  $xml = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
      $xml .= '<channel>' . "\n";
      $xml .= '<atom:link href="'.$siteUrl.'/rss.xml" rel="self" type="application/rss+xml" />';
      $xml .= '<title>' . $rssTitle["title"] . '</title>' . "\n";
      $xml .= '<link>' . $rssLink . '</link>' . "\n";
      $xml .= '<description>' . $rssDescription . '</description>' . "\n";

      // get RSS channel items
      $now =  date("YmdHis"); // get current time

      for ($i=0; $i < count($rssFeed); $i++) {

        $xml .= '<item>' . "\n";
        $xml .= '<title>' . $rssFeed[$i]['title'] . '</title>' . "\n";
        $xml .= '<link>' . $rssFeed[$i]['link'] . '</link>' . "\n";
        $xml .= '<description>' . $rssFeed[$i]['description'] . '</description>' . "\n";
        $xml .= '<guid>' . $siteUrl.str_replace(" ","%20",$rssFeed[$i]['id']) . '</guid>' . "\n";
        $xml .= '<pubDate>' . $rssFeed[$i]['pubDate'] . '</pubDate>' . "\n";
        if($rssFeed[$i]['category']){
          $xml .= '<category>' . $rssFeed[$i]['category'] . '</category>' . "\n";
        }
        $xml .= '</item>' . "\n";
      }

      $xml .= '</channel>';
      $xml .= '</rss>';
// echo $xml;
      $myfile = fopen($prefix."rss.xml", "w") or die("Unable to open file!");
      fwrite($myfile, $xml);
      fclose($myfile);
      // echo '{"success": "'.$path.'"}';
}

function generateRssJson(&$dropboxFiles){
  global $prefix;
  $rssFeed = array();
  $items = array();
  for ($i=0; $i < count($dropboxFiles); $i++) {
    $content = file_get_contents($dropboxFiles[$i]);
    $yaml = getBetween($content,"<!---","--->");
    $file = cleanFileName($dropboxFiles[$i]);
    $title = getBetween($yaml,"title: ","\n");
    $categories = getBetween($yaml,"categories: ","\n");
    $description = getBetween($yaml,"description: ","\n");
    $published = getBetween($yaml,"published: ","\n");
    $publishedDate = getBetween($yaml,"date: ","\n");
// echo $publishedDate."<br>";
    $date=date_create($publishedDate);
    $publishedDate = date_format($date,"Y-m-d\TH:i:sP");

    $item=array(
      "url"=>$file,
      "content_html"=>$description,
      "id"=>$file,
      "title"=>$title,
      "date_published"=>$publishedDate
    );
    array_push($items,$item);
  }

  $allitems=array(
    "version"=> "https://jsonfeed.org/version/1",
    "title"=> $siteTitle.": RSS Feed",
    "home_page_url"=> $prefix,
    "feed_url"=> $prefix."/feed.json",
    "items"=>$items
  );
  array_push($rssFeed,$allitems);

  $myJSON = json_encode($rssFeed);
  $myJSON = trim($myJSON, '[]');
// echo $myJSON;
  $myfile = fopen($prefix."feed.json", "w") or die("Unable to open file!");
  fwrite($myfile, $myJSON);
  fclose($myfile);

  // echo '{"success": "'.$path.'"}';
}

function generateBlogData(&$dropboxFiles){
  global $prefix;
  global $fullFileList;
  $allCategories = array();
  $writingList = array();
  $dbF = array_reverse($dropboxFiles);

  for ($i=0; $i < count($dbF); $i++) {
    $content = file_get_contents($dbF[$i]);
    $yaml = getBetween($content,"<!---","--->");
    if ($yaml) {
      $file = cleanFileName($dbF[$i]);
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
      $dropboxPath =str_replace($prefix."_dropbox",'',$dbF[$i]);
      $item=array(
        "title"=>$title,
        "fileName"=>str_replace('.md','.html',$file),
        "dropboxFileName"=>$dropboxPath,
        "categories"=>$postCategories,
        "date_published"=>$publishedDate
      );
      array_push($writingList,$item);
      array_push($fullFileList,str_replace('.md','.html',$file));
      }
    }
  }

  $postInfo = '{"categories":';
  $categoryJSON = json_encode($allCategories);
  $postInfo .= $categoryJSON;

  $postInfo .= ',"posts":';
  $myJSON = json_encode($writingList);
  $postInfo .= $myJSON;
  $postInfo .= '}';
  // echo $postInfo;

  $myfile = fopen($prefix."posts.json", "w") or die("Unable to open file!");
  fwrite($myfile, $postInfo);
  fclose($myfile);
  return $postInfo;
}

function generatePageData(&$dropboxFiles) {
  global $prefix;
  global $fullFileList;
  global $blogDirectory;
  $pages = array();

  listFF($prefix."_dropbox", $dropboxFolders, $dropboxFiles);
  for ($i=0; $i < count($dropboxFiles); $i++) {
    if (strpos($dropboxFiles[$i], $blogDirectory."/") !== false) {
      // skip blog posts (anthing with "blog/" in it's path)
    }
    else {
      $originalFile = $dropboxFiles[$i];
      $newFileMD = cleanFileName($dropboxFiles[$i]);
      $newFileHTML = renameMD($newFileMD);
      $pageEditDate = date("F d Y H:i:s",filemtime($prefix.$newFileHTML));

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
  $myJSON = json_encode($pages);
  $pageInfo .= $myJSON;
  $pageInfo .= '}';
  // echo $pageInfo;

  $myfile = fopen($prefix."pages.json", "w") or die("Unable to open file!");
  fwrite($myfile, $pageInfo);
  fclose($myfile);
  return $pageInfo;
}

function generateSitemap($fullFileList) {
  global $prefix;
  global $theme;
  global $siteTitle;
  global $siteUrl;
  global $twitterHandle;
  global $instagramHandle;
  global $copyright;
  $sitemap ='<?xml version="1.0" encoding="UTF-8"?>';
  $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

  for ($i=0; $i < count($fullFileList); $i++) {
     $sitemap .= '<url><loc>';
     $sitemap .= $prefix.$fullFileList[$i];
     $sitemap .= '</loc></url>';
  }
      $sitemap .= '</urlset>';
      // echo $sitemap;

      $myfile = fopen($prefix."sitemap.xml", "w") or die("Unable to open file!");
      fwrite($myfile, $sitemap);
      fclose($myfile);

      // If you want to output a sitemap.html that is more human readable uncomment this:
      // $sitemapHuman = file_get_contents($prefix."_themes/_shared/sitemap.html");
      // ob_start();
      // include $prefix."_themes/".$theme."/header.php";
      // $header = ob_get_clean();
      // ob_start();
      // include $prefix."_themes/".$theme."/footer.php";
      // $footer = ob_get_clean();
      // $sitemapHuman = $header.$sitemapHuman.$footer;
      // $mySitemap = fopen($prefix."sitemap.html", "w") or die("Unable to open file!");
      // fwrite($mySitemap, $sitemapHuman);
      // fclose($mySitemap);
      // echo $sitemapHuman;

      return $sitemap;
}

function generateArchive() {
  global $prefix;
  global $theme;
  global $siteTitle;
  global $siteUrl;
  global $twitterHandle;
  global $instagramHandle;
  global $copyright;
  $archive = file_get_contents($prefix."_themes/_shared/archive.html");
  ob_start();
  include $prefix."_themes/".$theme."/header.php";
  $header = ob_get_clean();
  ob_start();
  include $prefix."_themes/".$theme."/footer.php";
  $footer = ob_get_clean();
  $archive = $header.$archive.$footer;
  $myfile = fopen($prefix."archive.html", "w") or die("Unable to open file!");
  fwrite($myfile, $archive);
  fclose($myfile);
}

$fullFileList = array();
$dropboxFolders = array();
$dropboxFiles = array();

$dropboxPosts = listFF($prefix."_dropbox/".$blogDirectory, $dropboxFolders, $dropboxFiles);
generateRssJson($dropboxPosts);
generateRssXml($dropboxPosts);
generateBlogData($dropboxPosts);
generatePageData($dropboxFiles);
generateSitemap($fullFileList);
generateArchive();

 ?>
