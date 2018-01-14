<!---
title: Jquery Demo
date: Sun, 08 Jan 2018 12:00:00 MST
published: true
categories: 
jquery: true
--->

# Jquery Demo

Because Whisper is so committed to minimal markup, jQuery is not loaded by default. If you need to use jQuery on a page, add

	jquery: true
	
to your page or post's yaml front matter. This page has done just that, allowing the box below to grow when you click on it.

<div id="box" style="background-color: #98bf21; height:100px; width:100px; margin:20px auto;"></div>

<p><button id="btn1">Increase width</button> <button id="btn2">Decrease width</button></p>

<script>
$(document).ready(function(){
    $("#btn1").click(function(){
        $("#box").animate({width: "300px"});
    });
    $("#btn2").click(function(){
        $("#box").animate({width: "100px"});
    });
});
</script>