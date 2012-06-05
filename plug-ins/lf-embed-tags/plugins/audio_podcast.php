<?php

function plugin_audio_podcast( $url, $preview_url, $additional_args ) {

	$width     = array_key_exists( 'width',     $additional_args ) ? $additional_args['width']     : '280px';
	$height    = array_key_exists( 'height',    $additional_args ) ? $additional_args['height']    : '24px';
	$class     = array_key_exists( 'class',     $additional_args ) ? $additional_args['class']     : 'audiopodcastplayer';
	$autostart = array_key_exists( 'autostart', $additional_args ) ? $additional_args['autostart'] : 'false';
	$swfpath   = array_key_exists( 'swfpath',   $additional_args ) ? $additional_args['swfpath']
		: 'http://'.$_SERVER['SERVER_NAME'].preg_replace( '/index.php$/', '', $_SERVER['PHP_SELF'] );


	return '<object type="application/x-shockwave-flash" '
		. 'data="'.$swfpath.'app/jwplayer/player.swf" '
		. 'class="'.$class.'" style="width: '.$width.'; height: '.$height.';"> '
		. '<param name="allowScriptAccess" value="always"/>'
		. '<param name="allowFullScreen" value="true"/>'
		. '<param name="movie" value="'.$swfpath.'app/jwplayer/player.swf"/>'
		. '<param name="quality" value="high"/>'
		. '<param name="bgcolor" value="#ffffff"/>'
		. '<param name="flashvars" value="file='.$url.'&amp;autostart='.$autostart.'"/>'
		. '</object>';

}

bindPlayerPlugin( 'Audio Podcast', 'plugin_audio_podcast' );

?>
