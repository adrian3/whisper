<?php

// Change the setting below and rename this file to "config.php"

$siteTitle = "Whisper";
$siteUrl = "https://adrian3.github.io/steam/";
$copyright = "2018";
$blogDirectory = "blog"; // this needs to match the name of the folder in your dropbox folder where your blog posts live
$theme = "minimal"; // or "yourFolderName" (if you make a custom theme)
$password = ""; // optional but recommended. This is simply passed in the url to add a tiny bit of privacy to the admin screens

// Optional Dropbox Setup: Create an app within Dropbox Developer Portal: https://www.dropbox.com/developers/apps/
$dropboxKey = "";
$dropboxSecret = "";
$dropboxAccessToken = "";

$prefix = dirname(dirname(__FILE__))."/"; // don't change this unless you know what you are doing

function customFilter($html) {
  // This function is a hook into the html processor. Use it to manipulate your html as needed.
  return($html);
}

 ?>
