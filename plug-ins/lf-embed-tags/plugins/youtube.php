<?php

function plugin_youtube( $url, $preview_url ) {
	return $preview_url;
}

bindPlayerPlugin( 'youtube', 'plugin_youtube' );

?>
