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
    max-width: 28rem;
    margin: 0 auto;
  }
.dropboxResults {
  max-width: 28rem;
  margin: 0 auto;
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


<h2>Steam Admin</h2>

<p>
  <button class="button btn" onclick="syncDropbox();">Dropbox Sync</button>
  <button class="button btn" onclick="processAll();">Rebuild All</button>
  <!-- <button class="button btn" onclick="generateRSS();">Generate RSS</button> -->
</p>

<div class="dropboxResults"></div>

<h3>Pages:</h3>
<div class="pages"></div>

<h3>Posts:</h3>
<div class="posts"></div>

<hr>

<p>Want to automate the sync process? Schedule this <a href="https://code.tutsplus.com/tutorials/scheduling-tasks-with-cron-jobs--net-8800">Cron Job</a>:</p>
<pre><code><?php
echo "php -q ".$prefix."_admin/dropbox-sync.php password=".$password;
?></code></pre>

 <script type="text/javascript">

 function removePrefix(path){
   return(path.replace('<?php echo $prefix; ?>_dropbox/',''));
 }

 function classSafe(str){
   str = str.replace(/\s+/g, '');
   str = str.replace(".", '');
   str = str.replace(/\//g,'')
   return(str);
 }

var dropboxFiles = [];

function processAll(){
  for (var i = 0; i < posts.length; i++) {
    processFile(removePrefix(posts[i].dropboxFileName),"all");
  }
  for (var i = 0; i < pages.length; i++) {
    processFile(removePrefix(pages[i].dropboxFileName),"all");
  }
  setTimeout(function () {
    generateRSS();
  }, 1000); // give the files a little time to generate before generating the RSS just in case
}

 function processFile(filePath, allFlag) {
   $.getJSON('processor.php?password=<?php echo $password; ?>&path='+filePath, function(data) {
     console.log(data);
     $('.'+classSafe(filePath)).html(' <span style="margin:0 20px;">|</span>processing...');
     if (data.success) {
       $('.'+classSafe(filePath)).html(' <span style="margin:0 20px;">|</span>success');
     }
     else {
       $('.'+classSafe(filePath)).html(' <span style="margin:0 20px;">|</span>something went wrong');
     }
     });
     // re-generate the rss feeds for individual files, but not when batching all
     if (!allFlag) {
       generateRSS();
     }
 }

  function generateRSS() {
    $.getJSON('rss.php?password=<?php echo $password; ?>', function(data) {
    // console.log(data);
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
        shortDropboxFileName = removePrefix(posts[i].dropboxFileName);
        $('.posts').append('<div class="adminRow"><div class="column1">'+posts[i].title+' <br><a href="../'+posts[i].fileName+'">View</a> <span style="margin:0 20px;">|</span> <a onclick="processFile(\''+shortDropboxFileName+'\')">Rebuild</a> <span class='+classSafe(shortDropboxFileName)+'></span></div><div class="column2">Published: <br>'+formatDate(posts[i].date_published)+'</div></div>');
      }
    });
  }

    var pages = "";
    function getPages() {
      $.getJSON('../pages.json', function(data) {
        pages = data.pages;
        // console.log(pages);
        for (var i = 0; i < pages.length; i++) {
          shortDropboxFileName = removePrefix(pages[i].dropboxFileName);
          var pageTitle = pages[i].fileName+": \""+pages[i].title+"\"";
          if (pages[i].title=="") {
            pageTitle = pages[i].fileName;
          }
          $('.pages').append('<div class="adminRow"><div class="column1">'+pageTitle+' <br><a href="../'+pages[i].fileName+'">View</a> <span style="margin:0 20px;">|</span> <a onclick="processFile(\''+shortDropboxFileName+'\')">Rebuild</a> <span class='+classSafe(shortDropboxFileName)+'></span></div><div class="column2">Created: <br>'+formatDate(pages[i].pageEditDate)+'</div></div>');
        }
      });
    }

function syncDropbox() {
  $('.dropboxResults').html("<h3>Dropbox Sync:</h3><p>Sync initiated...</p>");
  $.getJSON('dropbox-sync.php?password=<?php echo $password; ?>', function(data) {
    downloaded = data.downloaded;
    deleted = data.deleted;
    if (downloaded=="undefined") {
      downloaded = 0;
    }
    if (deleted=="undefined") {
      deleted = 0;
    }
      $('.dropboxResults').html('<h3>Dropbox Sync:</h3><p>Sync complete: '+downloaded+' files downloaded, '+deleted+' files deleted</p>');
  });
}

getPosts();
getPages();

  </script>
<?php include "../_themes/".$theme."/footer.php"; ?>
