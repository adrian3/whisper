<!---
title: Steam Setup
--->

# Steam Setup

Steam is a minimal cms powered by Dropbox and Markdown. The concept is simple. You save markdown into a Dropbox folder and Steam generates a website.


## System Requirements
For this to work you need a server with php. Any shared hosting plan should be able to handle it. 


## Installation
First download or clone the [Steam git repository](https://github.com/adrian3/steam). You will note that Steam is made up of three folders, each of which is important. The _admin folder contains a config file that you will edit (see below), the main page where you will manage the syncing (index.php?password=yourpassword), and all the vendor files (which you shouldn't have to mess with).

Copy the Steam folders (_admin, _dropbox, and _themes) to your server, probably in the root directory if you want Steam to power your homepage. You can delete all the demo files in the _dropbox folder (this is where Steam will put the files it syncs from your Dropbox account). You can also delete the files in the root so all you are left with are the three Steam folders.

Modify the contents of _admin/config-SAMPLE.php then rename it to config.php. [For more information about the config.php file, go here](config.html).

Next you will probably want to change the navigation in the header. To do this, modify the _themes/minimal/nav.php file to suit your needs.

Create an app in Dropbox ([Full Dropbox instructions](dropbox.html)) and edit the dropbox credentials in the "config.php" file.

Put some markdown files into your dropbox folder. 

Visit www.YourWebsite.com/_admin/?password=YourPassword (replace "YourPassword" with whatever you set in the config.php file). Next, click the "sync" button to initiate the first sync between Dropbox and your Steam installation. This will copy all your Dropbox files down and process them into HTML.


## Syncing
Whenever you change a Dropbox file it needs to sync and process the file. You do this by visiting www.YourWebsite.com/_admin/?password=YourPassword or you can setup a cron job to run this task automatically at intervals you define. The cron job would look something like this:

	php -q /Users/username/full/path/to/_admin/dropbox-sync.php password=YourPassword

If you visit "/_admin/index.php?password=YourPassword" notice that at the bottom I try to generate what I think should be the correct path for your cron job.


## Front Matter
Your markdown files can start with some data that looks like this:

	<!---  
	title: Your Amazing Title  
	date: Saturday, 21 Jan 2017 10:18:34 MST  
	published: true  
	--->

This is completely optional, but this data will populate different parts of the template files. The "title" is used to generate the page title. The "date" is only used for blog posts. You can change "published" field to false if you aren't ready to publish the page yet.  

If you have used yaml front matter before you will note one difference. Yaml usually starts with "---" and ends with "---". My implementation changes it slightly to start with "<!---" and end with "--->". The reason for this difference if you haven't already figured it out is that this is how you comment out code in html. By commenting out the code it lets us have the benefits of yaml without the yaml actually appearing on the page.


## Gotchas
You should know that once an HTML file is generated on your server it will not be deleted unless you physically delete it. This is for protection because I don't want to blow away files programmatically.

Because Steam doesn't delete any HTML files after they are created, you may find yourself in a situation where you change a markdown file's YAML from "published: true" to "published: false" thinking this will make the file invisible to visitors on your site. Because the html file is already generated and you didn't manually delete it, it is technically still visible if users navigate directly to that file.

## Reserved File Names
There are certain file names that you should avoid because they will collide with files that steam creates dynamically. They are:

File names to avoid:

	archive.md
	archive.html
	humans.txt
	feed.json
	posts.json
	pages.json
	rss.xml
	sitemap.xml
	sitemap.md
	sitemap.html

Folder names to avoid:

	_admin
	_dropbox
	_themes
	_blog


#Running Locally
If you want to run Steam locally you need to have php running. I am a Mac user so I usually use MAMP but another method is to open the terminal, cd to the directory where your project lives, then type this:

	php -S localhost:8000

Then in your browser you should be able to go to: localhost:8000 and view your website.