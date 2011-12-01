<?php

require_once( dirname(__FILE__).'/lf_authetification.php' );
require_once( dirname(__FILE__).'/lf_data.php' );

$uid = lf_request_http_auth_basic();

//header( 'Content-Type: text/plain' );
//
//$uid = lf_check_authetification( 'test', 'uosmedia' );
//
//echo 'Trying to authetificate user »test« (passwd: uosmedia): '.( $uid < 0 ? 'FAILED' : 'SUCEEDED' )."\n";
//echo 'Current user ID: '.$uid."\n";
//
//$uid = lf_check_authetification( 'virtmm', 'uosmedia' );
//
//echo 'Trying to authetificate user »virtmm« (passwd: uosmedia): '.( $uid < 0 ? 'FAILED' : 'SUCEEDED' )."\n";
//echo 'Current user ID: '.$uid."\n";
//
//$a = lf_check_access( $uid, '/' );
//echo "Trying to access »/«:\n"
//	."\tRead  access: ".( $a[ 'r' ] >= 0 ? 'YES'.", level ".$a[ 'r' ] : 'NO' )."\n"
//	."\tWrite access: ".( $a[ 'w' ] >= 0 ? 'YES'.", level ".$a[ 'w' ] : 'NO' )."\n"
//	."\tApplied rule: ".$a[ 'rule_path' ]."\n";
//
//$a = lf_check_access( $uid, '/mediaobject' );
//echo "Trying to access »/mediaobject«:\n"
//	."\tRead  access: ".( $a[ 'r' ] >= 0 ? 'YES'.", level ".$a[ 'r' ] : 'NO' )."\n"
//	."\tWrite access: ".( $a[ 'w' ] >= 0 ? 'YES'.", level ".$a[ 'w' ] : 'NO' )."\n"
//	."\tApplied rule: ".$a[ 'rule_path' ]."\n";
//
//$a = lf_check_access( $uid, '/series/' );
//echo "Trying to access »/series/«:\n"
//	."\tRead  access: ".( $a[ 'r' ] >= 0 ? 'YES'.", level ".$a[ 'r' ] : 'NO' )."\n"
//	."\tWrite access: ".( $a[ 'w' ] >= 0 ? 'YES'.", level ".$a[ 'w' ] : 'NO' )."\n"
//	."\tApplied rule: ".$a[ 'rule_path' ]."\n";
//$a = lf_check_access( $uid, $_REQUEST[ 'path' ] );
//echo "Trying to access »".$_REQUEST[ 'path' ]."«:\n"
//	."\tRead  access: ".( $a[ 'r' ] >= 0 ? 'YES'.", level ".$a[ 'r' ] : 'NO' )."\n"
//	."\tWrite access: ".( $a[ 'w' ] >= 0 ? 'YES'.", level ".$a[ 'w' ] : 'NO' )."\n"
//	."\tApplied rule: ".$a[ 'rule_path' ]."\n";
//
//echo $_SERVER[ 'REQUEST_METHOD' ]."\n";
//print_r( $_REQUEST )."\n";

$access = lf_check_access( $uid, $_REQUEST[ 'path' ] );

//$access['uid'] = $uid;
//echo json_encode( $access );
//exit;

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
	header( 'Content-Type: application/json' );
	echo json_encode( $result );
}

?>
