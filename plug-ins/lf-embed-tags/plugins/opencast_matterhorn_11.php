<?php

function plugin_opencast_matterhorn_11( $url, $preview_url, $additional_args ) {

	$width  = array_key_exists( 'width',  $additional_args ) ? $additional_args['width']  : '300px';
	$height = array_key_exists( 'height', $additional_args ) ? $additional_args['height'] : '260px';
	$class  = array_key_exists( 'class',  $additional_args ) ? $additional_args['class']  : 'matterhornplayer11';

	return '<iframe scrolling="no" src="' . $preview_url . '" '
		. 'style="border: none; width: '.$width.'; height: '.$height.';" '
		. 'name="Opencast Matterhorn - Media Player" '
		. 'class="'.$class.'"/>';

}

bindPlayerPlugin( 'Opencast Matterhorn 1.1', 'plugin_opencast_matterhorn_11' );

?>
