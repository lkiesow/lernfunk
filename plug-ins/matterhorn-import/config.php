<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Lars Kiesow

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

/* The access-id the mediaobjects should have */
define( 'ACCESS_ID', 1 );

// set database access
$mysql = array(
      'server' => '___',
      'user'   => '___',
      'passwd' => '___',
      'db'     => 'lernfunk'
   );

/* Server adress to use if no server entry was in the request */
$default_server = 'http://video.virtuos.uni-osnabrueck.de:8080/';

// Set debug state
if (!defined('__DEBUG__'))
	define('__DEBUG__', false);

?>
