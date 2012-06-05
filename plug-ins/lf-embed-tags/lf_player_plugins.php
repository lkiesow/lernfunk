<?php

//header( 'Content-Type: text/plain; charset=utf-8' );
/* Disable caching for testing */
//header( 'Cache-Control: no-cache' );


$plugins = Array();


function bindPlayerPlugin( $format_name, $plugin_function ) {

	global $plugins;
	if ( array_key_exists( $format_name, $plugins ) ) {
		return FALSE; // name already bound.
	}
	$plugins[ $format_name ] = $plugin_function;
	return TRUE;

}

function include_player_plugins( $dir ) {

	$cats = scandir( $dir );

	foreach( $cats as $id => $cat ) {
		if ( $cat[0] == '.' ) {
			continue;
		}
		if (is_dir( $dir.'/'.$cat )) {
			include_player_plugins( $dir.'/'.$cat );
		} elseif ( substr( $cat, -4, 4 ) == '.php' ) {
			include_once( $dir.'/'.$cat );
		}
	}

}

include_player_plugins( dirname(__FILE__).'/plugins' );

?>
