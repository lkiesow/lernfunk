<?php

  //print_r($_FILES);
	require_once(dirname(__FILE__).'/config.php');

	$id    = $_REQUEST['id'];
	$field = $_REQUEST['field'];
	if (array_key_exists( 'uploadedfile', $_FILES ) ) {
		$info = pathinfo($_FILES['uploadedfile']['name']);
		$uploadfile = $uploaddir.$id.'.'.$info['extension'];
		if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
		  header( "location: upload_complete.php?fileurl=".$uploadurl.$id.'.'.$info['extension'].'&localurl='.$uploadfile.'&field='.$field ); 
?>
 
<?php
		}
	}  
?>

<html>
<head>
<link rel="stylesheet" href="../gfx/style.css" type="text/css" media="screen" />
</head>
<body style="text-align: center;">
<form enctype="multipart/form-data" action="upload.php?field=<?php echo $field; ?>&id=<?php echo $id; ?>" method="POST" style="margin-top: 200px;">
<input name="uploadedfile" type="file" />
<input type="submit" value="Upload" />
</form>
</body>
</html>
