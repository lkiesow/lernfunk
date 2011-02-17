<?php
	require_once(dirname(__FILE__).'/config.php');
?>
<html>
<head>
<?php
	$id    = $_REQUEST['id'];
	$field = $_REQUEST['field'];
	if (array_key_exists( 'uploadedfile', $_FILES ) ) {
		$info = pathinfo($_FILES['uploadedfile']['name']);
		$uploadfile = $uploaddir.$id.'.'.$info['extension'];
		if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
?>
<script type="text/javascript">
	window.parent.document.getElementById('uploadform').style.display = 'none';
	window.parent.document.getElementById('<?php echo $field; ?>').value = 
		'<?php echo $uploadurl.$id.'.'.$info['extension']; ?>';
</script>
<?php
		}
	}
?>
</head>
<body>
<form enctype="multipart/form-data" 
	action="upload.php?field=<?php echo $field; ?>&id=<?php echo $id; ?>" 
	method="POST">
<input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload" />
</form>
</body>
</html>
