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

	// include configuration
	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/mapper.php');

	header('Content-Type: text/plain; charset=utf-8');
	
	if (!array_key_exists( 'portal_url', $_GET ))
		die( 'Usage: '.$_SERVER['REQUEST_URI'].'?portal_url=...[&stream_id=...]' );

//	header( 'HTTP/1.1 301 Moved Permanently' );
	header( 'Location: '.map_request( $_REQUEST ) );

?>
