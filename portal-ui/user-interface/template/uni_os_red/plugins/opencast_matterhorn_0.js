function plugin_opencast_matterhorn_0( site, url, preview_url ) {
	
	var embed_url = url.replace(/watch.html/, 'embed.html');
	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<iframe src="' + embed_url + '" '
			+ 'style="border: none; width: 100%; height: 404px;" '
			+ 'name="Opencast Matterhorn - Media Player"/>';
	} else if ( site.toLowerCase() == 'home' ) {
		return '<iframe src="' + embed_url + '" '
			+ 'style="width: 300px; height: 280px; border: none;"/>';
	}
}

bindPlayerPlugin( 'Opencast Matterhorn', plugin_opencast_matterhorn_0 );
