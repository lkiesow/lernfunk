<?php

require_once( dirname(__FILE__).'/lf_authetification.php' );
require_once( dirname(__FILE__).'/lf_data.php' );

$uid = lf_request_http_auth_basic();

$access = lf_check_access( $uid, $_REQUEST[ 'path' ] );

// function defination to convert array to xml
function array_to_xml( $arr, &$xml ) {
	foreach( $arr as $key => $value ) {
		if ( is_array( $value ) ) {
			if ( !is_numeric( $key ) ) {
				$subnode = $xml->addChild( $key );
				array_to_xml( $value, $subnode );
			}
			else{
				$subnode = $xml->addChild( 'elem' );
				array_to_xml( $value, $subnode );
			}
		} else {
			if ( is_numeric( $key ) ) {
				$xml->addChild( 'elem', $value );
			} else {
				$xml->addChild( $key, $value );
			}
		}
	}
}

/* Return readme if we do not have any valid request parameter. */
if ( !array_key_exists( 'path', $_REQUEST ) 
	|| $_REQUEST[ 'path' ] == '' 
	|| $_REQUEST[ 'path' ] == '/' )
{
	header( 'Content-Type: text/plain' );
	include( 'readme' );
	exit;
}

$result = '';
/* Get data from webserver. */
if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' ) {
	$result = lf_parse_path_get( $access, $_REQUEST[ 'path' ], $_REQUEST['filter'],
		$_REQUEST['limit'], $_REQUEST['order'], $_REQUEST[ 'detail' ] );

/* Create new datasets on webserver. */
} elseif ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
	$result = lf_parse_path_post( $access, $_REQUEST[ 'path' ], $_REQUEST['data'] );

} else {
	header( 'Content-Type: text/plain' );
	echo 'Method “'.$_SERVER[ 'REQUEST_METHOD' ].'” is not available.';
}

if ( $_REQUEST['format'] == 'xml' ) {
	header( 'Content-Type: application/xml' );
	$xml = new SimpleXMLElement( '<?xml version="1.0"?>'
		.'<lernfunk_request></lernfunk_request>' );
	array_to_xml( $result, $xml );
	print $xml->asXML();
} else {
	if ( array_key_exists( 'jsonp', $_REQUEST ) ) {
		header( 'Content-Type: application/javascript' );
		echo $_REQUEST['jsonp'].'('.json_encode( $result ).');';
	} else {
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
	}
}

?>
