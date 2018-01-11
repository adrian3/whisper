<!---
title: Dropbox Sync Setup
--->

# Dropbox Sync Setup

Steam is meant to be connected to Dropbox, but this is optional. You _could_ just put markdown file manually into the _dropbox folder and let Steam process them. 

In the future I may automate the Dropbox setup process, but for now you need to jump throuh a few hoops. Sorry. It shouldn't be too bad. Here is what you need to do.


## How to Configure Dropbox

Go to:
[https://www.dropbox.com/developers/apps](https://www.dropbox.com/developers/apps)

Click on "Create App"

When it asks you to choose an API, select "Dropbox API" 

When it asks you to choose the type of access you need, select "App folder." (You could select "Full Dropbox," but most likely your site will be served out of a single folder.)

Dropbox will ask you name your app. This will be the name of the folder that gets created in your Dropbox folder. You can name it whatever you want as long as the name isn't taken already.

You will see an "App key" and you can click "show" to see your "App Secret." You will also need an access token. Click the "Generate" button below "Generated access token" 

Copy these three numbers and add them to your config.php between the quotation marks where it says:

	$dropboxKey = "";  
	$dropboxSecret = "";  
	$dropboxAccessToken = "";  

That's it. You shouldn't need to touch any other settings from Dropbox. 

To make sure everything is working, visit your [dropbox-sync.php](/_admin/dropbox-sync.php) page. You should see your files getting processed. 