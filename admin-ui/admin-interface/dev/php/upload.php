<?php

require_once(dirname(__FILE__).'/config.php');


/*
if ( (!array_key_exists('user', $_REQUEST)) ||
	(!array_key_exists('passwd', $_REQUEST)) ||
	($_REQUEST['user'] != __ADMIN_USER__) ||
	($_REQUEST['passwd'] != __ADMIN_PASSWD__) )
	die('error');
 */

if (!array_key_exists('id', $_REQUEST))
	die('error');

$id = $_REQUEST['id'];
$uploadfile = $uploaddir.$id.basename($_FILES['file0']['name']);

if (move_uploaded_file($_FILES['file0']['tmp_name'], $uploadfile)) {
  echo "success";
} else {
  // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
  // Otherwise onSubmit event will not be fired
	//echo "\n".$_FILES['file0']['tmp_name']."\n";
	//echo "\n".$uploadfile."\n";
  echo "error";
}
