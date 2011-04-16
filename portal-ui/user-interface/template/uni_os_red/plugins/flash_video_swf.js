function plugin_flash_video_swf( site, url ) {

	if ( site.toLowerCase() == 'seriesdetails' ) {
		return '<iframe src="' + url + '" '
			+ 'style="border: none; width: 100%; height: 404px;" '
			+ 'name="Opencast Matterhorn - Media Player"/>';

	} else if ( site.toLowerCase() == 'home' ) {
		return '<iframe src="' + url + '" '
			+ 'style="width: 300px; height: 280px; border: none;"/>';
	}

}

bindPlayerPlugin( 'Flash-Video (swf)', plugin_flash_video_swf );
