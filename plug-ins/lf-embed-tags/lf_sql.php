<?php
require_once( dirname(__FILE__).'/lf_config.php' );

function lf_query( $query ) {

	global $mysql;
	$sql = mysql_connect($mysql['server'], $mysql['user'], $mysql['passwd']);
	if (!$sql) {
		header( 'HTTP/1.1 500 Internal Server Error' );
		die( json_encode( array(
			'type'          => 'error', 
			'errtype'       => 'sql_error', 
			'errmsg'        => 'Could not connect to server.', 
			'sql_statement' => $query
		) ) );
	}
	if (! mysql_select_db($mysql['db'], $sql) ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
		die( json_encode( array(
			'type'          => 'error', 
			'errtype'       => 'sql_error', 
			'errmsg'        => 'Could not access database', 
			'sql_statement' => $query
		) ) );
	}

	mysql_set_charset('utf8', $sql);

	$result = mysql_query($query, $sql);
	if ( !$result ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
		die( json_encode( array(
			'type'          => 'error', 
			'errtype'       => 'sql_error', 
			'errmsg'        => mysql_error(), 
			'sql_statement' => $query
		) ) );
	}

	return $result;

}

?>
