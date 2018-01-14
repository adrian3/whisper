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

$dropboxPosts = listFF($prefix."_dropbox/".$blogDirectory);

$pages = generatePageData($dropboxFiles);
$posts = generateBlogData($dropboxPosts);

$siteData = '{"pages":';
$siteData .= $pages;
$siteData .= ',';
$siteData .= '"posts":';
$siteData .= $posts;
$siteData .= '}';
echo $siteData;

 ?>
