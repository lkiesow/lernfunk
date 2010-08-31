tpl = {};

tpl.home = {};

/*
tpl.home.videoplayer = '	<object type="application/x-shockwave-flash"\n'
	+ '		data="app/hd-player/Videodisplay.swf"\n'
	+ '		style="width: 280px; height: 210px;">\n'
	+ '		<param name="allowScriptAccess" value="always" />\n'
	+ '		<param name="allowFullScreen" value="true" />\n'
	+ '		<param name="movie" value="app/hd-player/Videodisplay.swf" />\n'
	+ '		<param name="quality" value="high" />\n'
	+ '		<param name="bgcolor" value="#ffffff" />\n'
	+ '		<param name="flashvars" value="video_url=(:url:)'
	+        '&autoplay=false&defaultZoomButton=false&videoSizeZoomButton=false'
	+        '&zoomOutButton=false&zoomInButton=false" />\n'
	+ '	</object>';
*/
tpl.home.videoplayer = 
	  '	<object id="player" type="application/x-shockwave-flash"\n'
	+ '		data="app/jwplayer/player.swf" style="width: 280px; height: 210px;">\n'
	+ '		<param name="allowScriptAccess" value="always"/>\n'
	+ '		<param name="allowFullScreen" value="true"/>\n'
	+ '		<param name="movie" value="app/jwplayer/player.swf"/>\n'
	+ '		<param name="quality" value="high"/>\n'
	+ '		<param name="bgcolor" value="#ffffff" />\n'
	+ '		<param name="flashvars" value="file=(:url:)" />\n'
	+ '	</object>';
tpl.home.audioplayer  = '<p>'
	+ '<object type="application/x-shockwave-flash" \n'
	+ '	data="app/audio-player/player.swf" \n'
	+ '	style="width: 320px; height: 60px;">\n'
	+ '	<param name="movie" value="app/audio-player/player.swf" />\n'
	+ '	<param name="quality" value="high" />\n'
	+ '	<param name="bgcolor" value="#ffffff" />\n'
	+ '	<param name="flashvars" value="soundFile=(:url:)&animation=no&autostart=yes" />\n'
	+ '</object>\n'
	+ '</p>';
tpl.home.virtpresenterplayer = '<iframe src="(:url:)" style="width: 420px; height: 360px; border: none;"></iframe>';

tpl.home.new_recording = '<div class="new_recording">(:mediatype:): (:title:)'
+ '<div style="font-size: smaller;">(:date:)</div>'
+ '	<p style="cursor: pointer; text-align: center;" >'
+ '			<img src="(:img:)" alt="(:title:)" style="max-width: 280px;" '
+ '				onclick=" replaceBy( this.parentNode, \'(:mediatype:)\', \'(:url:)\'); "/>'
+ '	</p>' 
+ '</div>';
tpl.home.series_update = '<div class="series_update">'
	+ '<a href="#resultfilter=series&cmd=search&filter=(:seriesname:)&details=1&mediatype=series&identifier=(:id:)">(:seriesname:)</a>\n'
	+ '	<div style="font-size: smaller;">(:desc:)</div>\n'
	+ '</div>\n';

tpl.details = {};
tpl.details.videoplayer = 
	  '	<object id="player" type="application/x-shockwave-flash"\n'
	+ '		data="app/jwplayer/player.swf" style="width: 480px; height: 270px;">\n'
	+ '		<param name="allowScriptAccess" value="always"/>\n'
	+ '		<param name="allowFullScreen" value="true"/>\n'
	+ '		<param name="movie" value="app/jwplayer/player.swf"/>\n'
	+ '		<param name="quality" value="high"/>\n'
	+ '		<param name="bgcolor" value="#ffffff" />\n'
	+ '		<param name="flashvars" value="file=(:url:)" />\n'
	+ '	</object>';

tpl.seriesdetails = {};

tpl.seriesdetails.recording = '<tr><td>(:title:)</td><td>(:desc:)</td><td>(:link:)</td></tr>\n';
tpl.seriesdetails.rec_link = '<a style="padding: 3px;" href="javascript: getDetails(\'(:mediatype:)\', (:obj_id:));">(:format:)</a> ';
