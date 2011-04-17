<?php

header('Content-Type: text/plain; charset=utf-8');
include_once( dirname(__FILE__).'/feed.php' );

if ( !array_key_exists( 'series', $_REQUEST ) ) {
	die( 'ERROR     : You must specify a series…' );
}
LFFeedGenerator::createFeed( intval($_REQUEST[ 'series' ]) );


?>
