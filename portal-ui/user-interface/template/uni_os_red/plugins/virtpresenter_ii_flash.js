function plugin_virtpresenter_ii_flash( site, url, preview_url ) {

	var flashvars = '';
	var rtmp = preview_url.match( /^rtmp:\/\/[^&]+&url=.*$/ );
	if (rtmp) {
		rtmp = rtmp[0].split( '&' );
		flashvars = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
	}

	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<object id="player" type="application/x-shockwave-flash" '
			+ 'data="app/jwplayer/player.swf" style="width: 100%; height: 360px;"> '
			+ '<param name="allowScriptAccess" value="always"/>'
			+ '<param name="allowFullScreen" value="true"/>'
			+ '<param name="movie" value="app/jwplayer/player.swf"/>'
			+ '<param name="quality" value="high"/>'
			+ '<param name="bgcolor" value="#ffffff"/>'
			+ '<param name="flashvars" value="file=' + flashvars + '&amp;autostart=true"/>'
			+ '</object>';

	} else if ( site.toLowerCase() == 'home' ) {
		return '<object id="player" type="application/x-shockwave-flash" '
			+ 'data="app/jwplayer/player.swf" style="width: 280px; height: 210px;"> '
			+ '<param name="allowScriptAccess" value="always"/>'
			+ '<param name="allowFullScreen" value="true"/>'
			+ '<param name="movie" value="app/jwplayer/player.swf"/>'
			+ '<param name="quality" value="high"/>'
			+ '<param name="bgcolor" value="#ffffff"/>'
			+ '<param name="flashvars" value="file=' + flashvars + '&amp;autostart=true"/>'
			+ '</object>';
	}

}

bindPlayerPlugin( 'virtPresenter II (Flash)', plugin_virtpresenter_ii_flash );
