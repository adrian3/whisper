<!---
title: How To Make A Post With Whisper
date: Sat, 07 Jan 2018 12:00:00 MST
published: true
categories: Whisper, How-To, Blog
--->

# How To Make A Blog Post With Whisper

Blog posts are created from Markdown files saved in you blog folder in Dropbox. You can change the name of this folder to whatever you want, just remember to update the config.php file variable $blogDirectory to match the name of your folder.

I like to organize my blog posts by year, so I create folders for each year and put my markdown files inside these folders. That is just a personal preference, however. You could put all your posts in the main blog folder directly, or go crazy with sub-folders by year, month, or whatever.

Blog posts need to include data that describes the post. This is called front matter or YAML. It looks like this:

	<!---
	title: Put Your Title Here  
	date: Sun, 13 Nov 2017  
	published: false  
	categories: Whisper, How-To, Blog  
	--->

The title is what will appear in lists of your blog posts and will also be inserted in the HTML's `<title>` tags. The date should be the date that the post was published. Note the date format. It is a bit rigid, but future versions of Whisper might try to be a little looser with this field.

If you aren't ready to publish the post yet, set published to false. Otherwise this field is optional. Note that changing a post from "published: true" to "published: false" after a post has been generated does not delete it from your site. This is because Whisper tries to be non-destructive, so it doesn't delete html files.

If you want to specify categories, this is simply a comma separated list. 

## RSS
When Whisper syncs a new file from Dropbox it recreates the RSS feeds. Two flavors of RSS are generated, XML and JSON. 