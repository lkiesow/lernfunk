<?php

header( 'Content-Type: text/plain; charset=utf-8' );
/* Disable caching for testing */
//header( 'Cache-Control: no-cache' );

require_once( dirname(__FILE__).'/config.php' );



function player_plugins( $dir ) {

	$cats = scandir( $dir );

	foreach( $cats as $id => $cat ) {
		if ( $cat[0] == '.' ) {
			continue;
		}
		if (is_dir( $dir.'/'.$cat )) {
			player_plugins( $dir.'/'.$cat );
		} elseif ( substr( $cat, -3, 3 ) == '.js' ) {
			echo file_get_contents( $dir.'/'.$cat )."\n";
		}
	}

}

player_plugins( dirname(__FILE__).'/../template/'.$cfg['tplName'].'/plugins' );

?>
