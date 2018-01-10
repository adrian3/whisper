<?php
require_once "config.php";
if ($password !== $_GET['password']) { die("Wrong password."); }
require_once "../_themes/".$theme."/header.php";
require_once 'functions.php';
 ?>

 <link rel="stylesheet" href="../_themes/minimal/css/steam.css"/>

<style media="screen">
  .adminRow {
    padding: 10px 0;
    border-top-width: 1px;
    border-top-color: #ccc;
    border-top-style: solid;
    overflow: hidden;
  }
.column1, .column2, .column3 {
  float:left;
}
.column1 {
  width: 75%;
}
.column2 {
  width: 25%;
}

</style>


<h2>Files synced from Dropbox:</h2>

<p>
  <button class="button btn" onclick="processAll();">Recreate All</button>
  <button class="button btn" onclick="generateRSS();">Generate RSS</button>
  <button class="button btn" onclick="window.open('dropbox-sync.php?password=<?php echo $password; ?>','_self');">Dropbox Sync</button>
</p>

<h3>Pages:</h3>
<div class="pages"></div>

<h3>Posts:</h3>
<div class="posts"></div>

 <script type="text/javascript">

 function removePrefix(path){
   return(path.replace('<?php echo $prefix; ?>_dropbox/',''));
 }

var dropboxFiles = [];

function processAll(){
  for (var i = 0; i < posts.length; i++) {
    processFile(posts[i].dropboxFileName);
  }
  for (var i = 0; i < pages.length; i++) {
    processFile(pages[i].dropboxFileName);
  }
}

 function processFile(filePath) {
   $.getJSON('processor.php?password=<?php echo $password; ?>&path='+removePrefix(filePath), function(data) {
     console.log(data);
     });
 }

  function generateRSS() {
    $.getJSON('rss.php?password=<?php echo $password; ?>', function(data) {
    console.log(data);
      });
  }

function formatDate(d){
  var date = Date.parse(d, "Y-m-d");
  date = new Date(d)
    var day = date.getDate();
    var month = new Array();
    month[0] = "Jan";
    month[1] = "Feb";
    month[2] = "Mar";
    month[3] = "Apr";
    month[4] = "May";
    month[5] = "Jun";
    month[6] = "Jul";
    month[7] = "Aug";
    month[8] = "Sep";
    month[9] = "Oct";
    month[10] = "Nov";
    month[11] = "Dec";
    var monthName = month[date.getMonth()];
    var year = date.getFullYear();
    var seconds = date.getSeconds();
    var minutes = date.getMinutes();
    var hours = date.getHours();
    var time = hours+":"+minutes+":"+seconds;
    return monthName+' '+day+', '+year+ " "+ time;
}

  var posts = "";
  function getPosts() {
    $.getJSON('../posts.json', function(data) {
      posts = data.posts;
      for (var i = 0; i < posts.length; i++) {
        $('.posts').append('<div class="adminRow"><div class="column1">'+posts[i].title+' <br><a href="../'+posts[i].fileName+'">View</a> <span style="margin:0 20px;">|</span> <a onclick="processFile(\''+removePrefix(posts[i].dropboxFileName)+'\')">Recreate</a></div><div class="column2">Published: <br>'+formatDate(posts[i].date_published)+'</div></div>');
      }
    });
  }
getPosts();

    var pages = "";
    function getPages() {
      $.getJSON('../pages.json', function(data) {
        pages = data.pages;
        console.log(pages);
        for (var i = 0; i < pages.length; i++) {
          var pageTitle = pages[i].fileName+": \""+pages[i].title+"\"";
          if (pages[i].title=="") {
            pageTitle = pages[i].fileName;
          }
          $('.pages').append('<div class="adminRow"><div class="column1">'+pageTitle+' <br><a href="../'+pages[i].fileName+'">View</a> <span style="margin:0 20px;">|</span> <a onclick="processFile(\''+removePrefix(pages[i].dropboxFileName)+'\')">Recreate</a></div><div class="column2">Created: <br>'+formatDate(pages[i].pageEditDate)+'</div></div>');
        }
      });
    }
getPages();

  </script>
<?php include "../_themes/".$theme."/footer.php"; ?>
