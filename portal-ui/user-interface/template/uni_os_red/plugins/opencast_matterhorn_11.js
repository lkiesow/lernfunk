function plugin_opencast_matterhorn_11( site, url, preview_url ) {

	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<iframe scrolling="no" src="' + preview_url + '" '
			+ 'style="border: none; width: 100%; height: 404px;" '
			+ 'name="Opencast Matterhorn - Media Player" '
			+ 'class="matterhornplayer11"/>';

	} else if ( site.toLowerCase() == 'home' ) {
		return '<iframe scrolling="no" src="' + preview_url + '&amp;play=true" '
			+ 'style="width: 300px; height: 260px; border: none;"/>';
	}

}

bindPlayerPlugin( 'Opencast Matterhorn 1.1', plugin_opencast_matterhorn_11 );
