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

function generateRssXml($dropboxFiles){
  global $prefix;
  global $siteTitle;
  global $siteUrl;
  $rssFeedCount = count($dropboxFiles);
  $rssFeed = array();
  $dropboxFiles = array_reverse($dropboxFiles);

  for ($i=0; $i < $rssFeedCount; $i++) {
    $content = file_get_contents($dropboxFiles[$i]);
    $yaml = getBetween($content,"<!---","--->");
    $file = cleanFileName($dropboxFiles[$i]);
    $file = str_replace(" ","%20",$file);
    $file = str_replace(".md",".html",$file);
    $title = getBetween($yaml,"title: ","\n");
    $categories = getBetween($yaml,"categories: ","\n");
    $description = getBetween($yaml,"description: ","\n");
    $published = getBetween($yaml,"published: ","\n");
    $publishedDate = getBetween($yaml,"date: ","\n");
    $date=date_create($publishedDate);
    $publishedDate = date_format($date,"D, d M Y H:i:s O");

    if ($published!=="false") {
      $item=array(
        "link"=>$siteUrl.$blogDirectory.'/'.$file,
        "source"=>$siteUrl."/".$blogDirectory.'/'.$file,
        "description"=>$description,
        "id"=>$file,
        "title"=>$title,
        "pubDate"=>$publishedDate,
        "category"=>$categories
      );
      array_push($rssFeed,$item);
    }
    else {
      $rssFeedCount++;
    }
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
        $xml .= '<guid>' . $siteUrl."/".str_replace(" ","%20",$rssFeed[$i]['id']) . '</guid>' . "\n";
        $xml .= '<pubDate>' . $rssFeed[$i]['pubDate'] . '</pubDate>' . "\n";
        if($rssFeed[$i]['category']){
          $xml .= '<category>' . $rssFeed[$i]['category'] . '</category>' . "\n";
        }
        $xml .= '</item>' . "\n";
      }

      $xml .= '</channel>';
      $xml .= '</rss>';

      $myfile = fopen($prefix."rss.xml", "w") or die("Unable to open file!");
      fwrite($myfile, $xml);
      fclose($myfile);
      // echo '{"success": "'.$path.'"}';
      return $xml;
}

function generateRssJson($dropboxFiles){
  global $prefix;
  global $siteUrl;
  $rssFeedCount = count($dropboxFiles);
  $rssFeed = array();
  $items = array();
  $dropboxFiles = array_reverse($dropboxFiles);

  for ($i=0; $i < $rssFeedCount; $i++) {
    $content = file_get_contents($dropboxFiles[$i]);
    $yaml = getBetween($content,"<!---","--->");
    $file = cleanFileName($dropboxFiles[$i]);
    $file = str_replace(" ","%20",$file);
    $file = $siteUrl."/".str_replace(".md",".html",$file);
    $title = getBetween($yaml,"title: ","\n");
    $categories = getBetween($yaml,"categories: ","\n");
    $description = getBetween($yaml,"description: ","\n");
    $published = getBetween($yaml,"published: ","\n");
    $publishedDate = getBetween($yaml,"date: ","\n");
    $date=date_create($publishedDate);
    $publishedDate = date_format($date,"Y-m-d\TH:i:sP");

    if ($published!=="false") {
      $item=array(
        "url"=>$file,
        "content_html"=>$description,
        "id"=>$file,
        "title"=>$title,
        "date_published"=>$publishedDate
      );
      array_push($items,$item);
    }
    else {
      $rssFeedCount++;
    }
  }

  $allitems=array(
    "version"=> "https://jsonfeed.org/version/1",
    "title"=> $siteTitle.": RSS Feed",
    "home_page_url"=> $siteUrl,
    "feed_url"=> $siteUrl."/feed.json",
    "items"=>$items
  );
  array_push($rssFeed,$allitems);

  $myJSON = json_encode($rssFeed, JSON_PRETTY_PRINT);
  $myJSON = trim($myJSON, '[]');
  $myfile = fopen($prefix."feed.json", "w") or die("Unable to open file!");
  fwrite($myfile, $myJSON);
  fclose($myfile);

  // echo '{"success": "'.$path.'"}';
  return $myJSON;
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
     $sitemap .= $siteUrl.$fullFileList[$i];
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
  $header = str_replace('<!-- {{jquery}} -->','<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>',$header);

  $footer = ob_get_clean();
  $archive = $header.$archive.$footer;
  $myfile = fopen($prefix."archive.html", "w") or die("Unable to open file!");
  fwrite($myfile, $archive);
  fclose($myfile);
}

$fullFileList = array();
$dropboxFiles = array();
$dropboxPosts = listFF($prefix."_dropbox/".$blogDirectory);
$dropboxFiles = array(); // clear the array before creating next list
$dropboxFiles = listFF($prefix."_dropbox/");
generateRssJson($dropboxPosts);
generateRssXml($dropboxPosts);
generateBlogData($dropboxPosts);
generatePageData($dropboxFiles);
generateSitemap($fullFileList);
generateArchive();

 ?>
