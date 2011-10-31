<?php

require_once( dirname(__FILE__).'/lf_sql.php' );

function lf_check_authetification( $user, $passwd ) {

	$uid = -1;

	$query    = 'select id from user '
		.'where user_name = "'.$user.'" and password = "'.md5( $passwd ).'";';
	if ($rs = lf_query($query)) {
		while ($r = mysql_fetch_object($rs)) {
			$uid = $r->id;
		}
	}
	return $uid;

}


function lf_check_access( $uid, $path ) {

	$access_level = array(
			'r' => -1,
			'w' => -1,
			'rule_path' => '/'
		);
	$query    = 'select path, level_read, level_write '
		.'from user_access where uid = "'.$uid.'";';
	if ($rs = lf_query($query)) {
		while ($r = mysql_fetch_object($rs)) {
			/* Check if $path starts with $r->path. */
			if ( strpos( $path, $r->path ) === 0 ) {
				/* Check if new path rule is more specific than old rule. */
				if ( strpos( $r->path, $access_level[ 'rule_path' ] ) === 0 ) {
					$access_level[ 'rule_path' ] = $r->path;
					$access_level[ 'r' ]         = $r->level_read;
					$access_level[ 'w' ]         = $r->level_write;
				}
			}
		}
	}
	if ( $access_level[ 'r' ] == '' ) {
		$access_level[ 'r' ] = -1;
	}
	if ( $access_level[ 'w' ] == '' ) {
		$access_level[ 'w' ] = -1;
	}
	return $access_level;
}

function lf_request_http_auth_basic() {

	$uid = isset( $_SERVER['PHP_AUTH_USER'] ) 
		? lf_check_authetification( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] )
		: -1;

	if ( $uid >= 0 ) {
		return $uid;
	}
	header( 'WWW-Authenticate: Basic realm="lernfunk::webservice"' );
	header('HTTP/1.0 401 Unauthorized');
	echo 'You have to be logged in to access the webservice.';
	exit();
	
}

?>
