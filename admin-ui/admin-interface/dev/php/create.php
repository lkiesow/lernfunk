<?php
require_once(dirname(__FILE__).'/config.php');
ini_set('display_errors', true);

// Original image
$imagename = $_POST['filename'];
$filename = $uploaddir."$imagename";
 
// Get dimensions of the original image
list($current_width, $current_height) = getimagesize($_POST['fileurl']);
 
// The x and y coordinates on the original image where we
// will begin cropping the image
$left = $_POST['totalwidth'] / $_POST['compressed_width'] * $_POST['x1'];
$top = $_POST['totalheight'] / $_POST['compressed_height'] * $_POST['y1'];
 
// This will be the final size of the image (e.g. how many pixels
// left and down we will be going)
$crop_width = ($_POST['totalwidth'] / $_POST['compressed_width']) * $_POST['width'];
$crop_height = $_POST['totalheight'] / $_POST['compressed_height'] * $_POST['height'];

// Resize to 300x300
// Resample the image

// Create JPEG Images
if ( preg_match( '/\.jpe{0,1}g/', $_POST['fileurl'], $match ) ) {
	$current_image = imagecreatefromjpeg($_POST['fileurl']);

// Create PNG Images
} elseif ( preg_match( '/\.png/', $_POST['fileurl'], $match ) ) {
	$current_image = imagecreatefrompng($_POST['fileurl']);

// Other file formats are currently not supported
} else {
	die( '<p>The file format of this image is currently not supported.</p>' );
}

$canvascropped = imagecreatetruecolor($crop_width, $crop_height);
$canvastop = imagecreatetruecolor(300, 210);

imagecopyresampled ($canvastop, $current_image, 0, 0, $left, $top, 300, 210, $crop_width, $crop_height);

// Create JPEG Images
if ( preg_match( '/\.jpe{0,1}g/', $previewfooter, $match ) ) {
	$watermark = imagecreatefromjpeg($previewfooter);

// Create PNG Images
} elseif ( preg_match( '/\.png/', $previewfooter, $match ) ) {
	$watermark = imagecreatefrompng($previewfooter);

} else {
	die( 'ERROR: Unsupported file format for footer.' );
}
$canvas = imagecreatetruecolor (300, 300);
    
imagecopy($canvas, $canvastop, 0, 0, 0, 0, 300, 210);
imagecopy($canvas, $watermark, 0, 210, 0, 0, 300, 90);



$new_filename = preg_replace('/\..+$/', '.' . "new.jpg", $filename);
$new_fileurl = preg_replace('/\..+$/', '.' . "new.jpg", $_POST['fileurl']);

//Bild speichern 
imagejpeg($canvas, $new_filename, 100);
imagedestroy($canvastop);     
imagedestroy($canvas);
imagedestroy($watermark);
imagedestroy($canvascropped);
imagedestroy($current_image);

$field = $_POST['field'];

?>
<!DOCTYPE html>
<html>
<head>
  <title>Fertig!</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">       
    <script type="text/javascript" src="../js/imports/prototype.js" type="text/javascript"></script>
    <script type="text/javascript" src="../js/imports/scriptaculous-js-1.9.0/src/scriptaculous.js?load=effects,builder,dragdrop" type="text/javascript"></script>
    <script type="text/javascript" src="../js/imports/jsCropperUI-1.2.2/cropper/cropper.js" type="text/javascript"></script>
    <link rel="stylesheet" href="../gfx/lfadmin.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="../gfx/style.css" type="text/css" media="screen" />
		
    <script type="text/javascript">
		function insert_and_close() {
	      window.parent.document.getElementById('uploadform').style.display = 'none';
			window.parent.document.getElementById('<?php echo $field; ?>').value = 
		     '<?php echo $new_fileurl; ?>';
			window.parent.document.getElementById('savebutton').disabled = false;
			history.go(-2);
		}
    </script>
</head>
<body>
 
    <div class="resultimage">
      <img id="uploadedImage" src="<?php echo $new_fileurl.'?time='.time(); ?>" />
     </div>  
      <input class="button2" style="float:left; margin-left: 48px; margin-top: 10px; width: 80px;" type="button" onclick="history.back();" name="zurück" value="zurück" />
      <input class="button2" style="float:right; margin-right: 48px; margin-top: 10px; width: 80px;" type="button" onclick="insert_and_close();" name="fertig" value="fertig" />        
   
    
</body>
</html>
