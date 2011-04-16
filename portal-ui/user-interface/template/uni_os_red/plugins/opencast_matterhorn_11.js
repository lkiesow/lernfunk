function plugin_opencast_matterhorn_11( site, url, preview_url ) {
	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<iframe src="' + preview_url + '" '
			+ 'style="border: none; width: 100%; height: 404px;" '
			+ 'name="Opencast Matterhorn - Media Player"/>';
	} else if ( site.toLowerCase() == 'home' ) {
		return '<iframe src="' + preview_url + '" '
			+ 'style="width: 300px; height: 280px; border: none;"/>';
	}
}

bindPlayerPlugin( 'Opencast Matterhorn 1.1', 'plugin_opencast_matterhorn_11' );
