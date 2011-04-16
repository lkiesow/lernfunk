function plugin_audio_podcast( site, url, preview_url ) {

	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<object id="player" type="application/x-shockwave-flash" '
			+ 'data="app/jwplayer/player.swf" style="width: 100%; height: 24px;">'
			+ '<param name="allowScriptAccess" value="always"/>'
			+ '<param name="allowFullScreen" value="true"/>'
			+ '<param name="movie" value="app/jwplayer/player.swf"/>'
			+ '<param name="quality" value="high"/>'
			+ '<param name="bgcolor" value="#ffffff"/>'
			+ '<param name="flashvars" value="file=' + url + '"/>'
			+ '</object>';

	} else if ( site.toLowerCase() == 'home' ) {
		return '<object id="player" type="application/x-shockwave-flash" '
			+ 'data="app/jwplayer/player.swf" style="width: 280px; height: 24px;">'
			+ '<param name="allowScriptAccess" value="always"/>'
			+ '<param name="allowFullScreen" value="true"/>'
			+ '<param name="movie" value="app/jwplayer/player.swf"/>'
			+ '<param name="quality" value="high"/>'
			+ '<param name="bgcolor" value="#ffffff"/>'
			+ '<param name="flashvars" value="file=' + url + '"/>'
			+ '</object>';
	}

}

bindPlayerPlugin( 'Audio Podcast', plugin_audio_podcast );
