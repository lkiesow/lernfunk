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


// Set debug state
define('__DEBUG__', false);

// set error mode
// you may consider to disable error messages in the release build
// but then useres cannot report any bugs properly
error_reporting(E_ALL);
ini_set("display_errors", TRUE);

// set the encoding of the output
// mb_internal_encoding('UTF-8');
// mb_http_output('UTF8');

// include lernfunk library
require_once( $_SERVER['DOCUMENT_ROOT'].'/libraries/base/lernfunk.php');

?>
