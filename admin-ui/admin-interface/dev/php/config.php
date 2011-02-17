<?php

// set error mode
// you may consider to disable error messages in the release build
// but then useres cannot report any bugs properly
error_reporting(E_ALL);
ini_set("display_errors", TRUE);

// set the encoding of the output
//mb_internal_encoding('UTF-8');
//mb_http_output('UTF8');

// include lernfunk library
include_once($_SERVER['DOCUMENT_ROOT'].'/libraries/base/lernfunk.php');

// emulate the built-in JSON extension of PHP 5.2
// maybe you have a new php version and don't need this anymore
// include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/xmlrpc.inc');
// include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/jsonrpc.inc');
// include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/json_extension_api.inc');

	$uploaddir = '/var/www/html/release/static/preview/';
	$uploadurl = 'http://lernfunk.de/static/preview/';
?>
