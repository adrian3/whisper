<?php
// This file is handy for generating a basic gallery of images. Simply drop this file in a folder of images. You probably want to rename it to index.php. Be sure to change the path below to match the relationship between the image folder to the root.
$folderBackTrack = "../../../"; 
include $folderBackTrack."_admin/config.php";
include $folderBackTrack."_themes/".$theme."/header.php";
?>

<style type="text/css">
a img {
	max-width: 33%;
	max-height: 200px;
	diaplay: inline-block;
	vertical-align: top;
	padding: 10px;
}
</style>

<?php
	function isImage($fileName){
		$supported_image = array(
		    'gif',
		    'jpg',
		    'jpeg',
		    'png'
		);
		$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		if (in_array($ext, $supported_image)) {
		  return true;
		} 
		else {
		  return false;
		}
	}

	$gallery_handle = opendir(dirname( realpath( __FILE__ ) ) . '/');
	$gallery_counter = 0;
	while( $gallery_file = readdir( $gallery_handle ) ) {
		if( $gallery_file !== '.' && $gallery_file !== '..' && isImage($gallery_file)) {
				$image = '<a href="';
				$image .= $gallery_file;
				$image .= '" title="File name:'; 
				$image .= $gallery_file;
				$image .= '"><img src="';
				$image .= $gallery_file;
				$image .= '" class="qt-photo-gallery-item-image" /></a>';
				echo $image;
		}
	}

	include $folderBackTrack."../../../_themes/".$theme."/footer.php";

 ?>