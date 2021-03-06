<?php

// set error mode
// you may consider to disable error messages in the release build
// but then useres cannot report any bugs properly
	error_reporting( E_ALL );
	ini_set( "display_errors", TRUE );

// set the encoding of the output
	// mb_internal_encoding('UTF-8');
	// mb_http_output('UTF8');

// Set default timezone
// For a list of valid options visit:
// http://de3.php.net/manual/en/timezones.php
date_default_timezone_set( 'Europe/Berlin' );

// include lernfunk library
	include_once($_SERVER['DOCUMENT_ROOT'].'/libraries/base/lernfunk.php');

// Set directory for upload and its URL. The closing slash (or backslash in
// case you use Windows) can _not_ be omittet.
	$uploaddir = '/var/www/html/release/static/preview/';
	$uploadurl = 'http://lernfunk.de/static/preview/';
	$previewfooter = 'http://lernfunk.de/gfx/uni-footer.jpg';

// Set access data for mysql database
$mysql = array(
	'server' => '',
	'user'   => '',
	'passwd' => '',
	'db'     => 'lernfunk'
);

?>
