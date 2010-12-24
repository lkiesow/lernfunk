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


	// check if debugmode is requested
	if ( array_key_exists('debug', $_REQUEST) && ($_REQUEST['debug'] == 'true') ) {
		define('__DEBUG__', true);
	}

	// include configuration and functionality
	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/matterhorn.php');

	// set contend-type according to mode
	if (__DEBUG__) {
		header('Content-Type: text/plain; charset=utf-8');
	} else {
		header('Content-Type: application/json; charset=utf-8');
	}
	
	// check if there is any data request passed to this script
	if (!array_key_exists('request', $_REQUEST) || !$_REQUEST['request'])
		die( json_encode( array(
				'type' => 'error', 
				'errtype' => 'request_error', 
				'errmsg' => 'No request data.', 
				'request_data' => $_REQUEST) ) );

	// try to decode the json to an assoc array
//		print_r( preg_replace( "/\n\s*/s", '', $_REQUEST['request']) );
//		print_r( json_decode( preg_replace( "/\n\s*/s", '', $_REQUEST['request']), true) );
//		echo "\n\n\n";
	$request = json_decode( $_REQUEST['request'], true );
	if (!is_array($request))
		die( json_encode( array(
				'type' => 'error', 
				'errtype' => 'request_error', 
				'errmsg' => 'Could not parse JSON data passed as request.', 
				'request_data' => $_REQUEST) ) );
		
	// check if access key is correct
	if (ACCESS_KEY != '')
		if (!array_key_exists('key', $request) || $request['key'] != ACCESS_KEY)
			die( json_encode( array(
				'type' => 'error',
				'errtype' => 'security_error',
				'errmsg' => 'Invalid access key \''
					.(is_array($request) && array_key_exists('key', $request) ? $request['key'] : '').'\'.') ) );
		
	// check if there is a command
	if (!array_key_exists('cmd', $request) || empty($request['cmd']))
		die( json_encode( array(
				'type' => 'error', 
				'errtype' => 'request_error', 
				'errmsg' => 'No request command.', 
				'request_data' => $_REQUEST) ) );

	// convert command to lowercase and check if it is a valid command
	$request['cmd'] = strtolower($request['cmd']);
	if ( !LFMatterhornInportQueue::isvalidcmd($request['cmd']) )
		die( json_encode( array(
				'type' => 'error', 
				'errtype' => 'request_error', 
				'errmsg' => 'Invalid request command \''.$request['cmd'].'\'.', 'request_data' => $_REQUEST) ) );

	// call the service function assosiated with the given command
	echo LFMatterhornInportQueue::$request['cmd']( $request );

	if (__DEBUG__) {
		echo "\n\n";
		echo $_REQUEST['request'];
		echo "\n\n";
		print_r($request);
	}

?>
