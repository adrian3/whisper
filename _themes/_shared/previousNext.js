var postTitle = document.title;
var posts = "";
function getPosts() {
  $.getJSON('/posts.json', function(data) {
    posts = data.posts;
    console.log(posts);
    for (var i = 0; i < posts.length; i++) {
      if (posts[i].title==postTitle) {
        arrayIndex = i;

        prevPostIndex = i-1;
        if (prevPostIndex>=0) {
          $('.previousPost').show();
          $('.previousPost').html('Previous: <a href="/'+posts[prevPostIndex].fileName+'">'+posts[prevPostIndex].title+'</a>');
        }

        nextPostIndex = i+1;
        if (nextPostIndex>0&&nextPostIndex<posts.length) {
          $('.nextPost').show();
          $('.nextPost').html('Next: <a href="/'+posts[nextPostIndex].fileName+'">'+posts[nextPostIndex].title+'</a>');
        }
        // console.log("Index ID: "+arrayIndex);

      }
      else {
        // console.log("Not in array");
      }
    }
  });
}
getPosts();
