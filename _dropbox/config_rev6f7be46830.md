<!---
title: About the Config File
published: true
--->

## About the Config File

While I tried to include comments within the config.php file, you might still have some questions. 


## Site Title, URL, and Copyright Variables
The $siteTitle variable is used in the header. The $siteUrl variable is used in a few places, so change this to be the full url of the home page of your steam powered website. The $copyright variable is used in the footer.

## Set Your Blog Directory
The $blogDirectory should be the name of the folder where you blog posts live. This is where Steam will look for blog posts.

## Theme Configuration
The $theme variable is the name of the folder where your theme files live. By default it uses the minimal theme, but you can create your own theme and change this variable to match the name of your new theme's folder.

## Password Protection
The $password variable is used to prevent Steam's admin pages from being publicly accessible. This is optional but recommended. The password you specify is simply passed in the url to add a tiny bit of privacy to your admin screens. Note that this password is visible in the URL of your admin pages, so it isn't _that_ secure. It is better than nothing, however.


## Dropbox Fields
For more info about what goes in the Dropbox section, ([view the full Dropbox instructions](dropbox.html))


## Custom Filters
You might notice a function called customFilter at the bottom of the config file. This is an advanced option that lets you modify the HTML as it is being modified by Steam. This could be handy if you wanted to insert extra HTML into your pages. 

For example, you might want to insert html from one markdown file inside another. In your markdown you could put {{snippet}} where you want the html to go then in the customFilter function look for {{snippet}} inside the $HTML and replace it with the contents of another file. This requires a little bit of php knowledge, but it is there if you need it.
