<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Nils Birnbaum, Lars Kiesow, Benjamin Wulff

	This file is part of Lernfunk. 

	Lernfunk is free software: you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation, either version 3 of the License, or 
	(at your option) any later version. 

	Lernfunk is distributed in the hope that it will be useful, 
	but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
	GNU General Public License for more details. 

	You should have received a copy of the GNU General Public License 
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>. 
*/


// Define  access key
define('ACCESS_KEY', '___');

// set database access
$mysql = array(
      'server' => 'mysql5.serv.uni-osnabrueck.de',
      'user' => '',
      'passwd' => '',
      'db' => 'lernfunk'
   );

// Set debug state
if (!defined('__DEBUG__'))
	define('__DEBUG__', false);

// Define valid access ids(which mediaobjects should be send)
// define('ACCESS_CONDITIONi'); // so far ignored

// set error mode
// you may consider to disable error messages in the release build
// but then useres cannot report any bugs properly
error_reporting(E_ALL);
ini_set("display_errors", TRUE);

// set the encoding of the output
mb_internal_encoding('UTF-8');
mb_http_output('UTF8');

// include lernfunk library
//require_once(dirname(__FILE__).'/../lflib/lernfunk.php');

// emulate the built-in JSON extension of PHP 5.2
// maybe you have a new php version and don't need this anymore
//include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/xmlrpc.inc');
//include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/jsonrpc.inc');
//include_once('/opt/pmwiki/local/studip_auth/vendor/phpxmlrpc/json_extension_api.inc');

?>
