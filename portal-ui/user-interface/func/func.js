﻿var dep_select_hide_interval = null;

var before_counter = '';
var after_counter  = '';

//var loadingHTML = 'loading...';

// all data we got from our last search
var lastSearch = null;

// only the data now displayed
var currData = null;

// templates who are already loaded
templates = {};

// these are the parameters of the current state
// (the ones that are in the hash)
currentState = {}

// buffer for going to last results
pagerBuffer   = [];
contentBuffer = [];
pStyleBuffer  = [];
seriesRecBuf  = [];

menu_intervall = null;

request_id = 0;

loading = {
		'series'     : false,
		'recordings' : false,
		'lecturer'   : false,
		'podcast'    : false
	}

/* storage for player plugins */
playerPlugin = {};

function validatePage() {
	
	/* Get page source */
	var pageSrc = document.getElementsByTagName( 'html' )[0].outerHTML;
	alert( pageSrc );

	/* Create form */
	var form = document.createElement( 'form' );
	form.setAttribute( 'method', 'post' );
	form.setAttribute( 'enctype', 'multipart/form-data' );
	form.setAttribute( 'action', 'http://validator.w3.org/check' );

	/* Create textarea */
	var texta = document.createElement( 'textarea' );
	texta.setAttribute( 'id', 'fragment' );
	texta.setAttribute( 'name', 'fragment' );
	texta.value = pageSrc;
	form.appendChild( texta );

//	form.submit();

}

/**
 * Takes JSON-data as string and returns it formatted
 *   json   SON-data as string
 **/
function formatJSON( json ) {
	
	indentCount = 0;
	status = 0;
	result = '';
	for ( i = 0; i < json.length; i++ ) {
		if ( status == 0 ) {
			if ( json[i] == '"' ) {
				result += '"';
				status = 1;
			} else if ( json[i] == '[' || json[i] == '{' ) {
				result += json[i] + '\n';
				indentCount++;
				for ( j = 0; j < indentCount; j++ ) {
					result += '\t';
				}
			} else if ( json[i] == ']' || json[i] == '}' ) {
				result += '\n';
				indentCount--;
				for ( j = 0; j < indentCount; j++ ) {
					result += '\t';
				}
				result += json[i];
			} else if ( json[i] == ',' ) {
				result += json[i] + '\n';
				for ( j = 0; j < indentCount; j++ ) {
					result += '\t';
				}
			} else {
				result += json[i];
			}
		} else if ( status == 1 ) {
			if ( json[i] == '"' ) {
				status = 0;
			}
			result += json[i];
		}
	}

	return result;

}

function alertj( o ) {
	alert( $.toJSON( o ) );
}

function alertfj( o ) {
	alert( formatJSON( $.toJSON( o ) ) );
}
/*
 * Initialize calendar
 **/
function calendar_init() {

	// get recdates
	if ( window.location.hash == '' ) {
		requestWebservices( {
				"cmd" : "getRecDates", 
				"args" : { 
					"year"  : '2011',
					"month" : '01'
				} 
			}, function( data ) {
				if (handleError( data )) {
					recdates = data.recdates;

					// calendar initialization
					$("#datepicker").datepicker({
							firstDay: 1,
							maxDate: '+0',
							monthNames: ['Januar', 'Februar', 'März', 
								'April', 'Mai', 'Juni', 
								'Juli', 'August', 'September', 
								'Oktober', 'November', 'Dezember'],
							monthNamesShort: ['Jan', 'Feb', 'Mar', 
								'Apr', 'Mai', 'Jun', 
								'Jul', 'Aug', 'Sep', 
								'Okt', 'Nov', 'Dez'],
							dayNames: ['Montag', 'Dienstag', 'Mittwoch', 
								'Donnerstag', 'Freitag', 'Samstag'],
							dayNamesMin: ['So', 'Mo', 'Di', 
								'Mi', 'Do', 'Fr', 'Sa'],
							dateFormat: 'yy-mm-dd',
							onSelect: function(dateText, inst) { 
									window.location.hash = '#cmd=search&date=' + dateText;
								},
							beforeShowDay: function( date ) {
									var year = recdates[date.getFullYear()];
									if ( year ) {
										var month = year[date.getMonth()+1];
										if ( month ) {
											if ( $.inArray( date.getDate() + '', month ) >= 0 ) {
												return { 0 : true, 1 : '' };
											}
										}
									}
									return { 0 : false, 1 : '' };
								}
						});

				}
			} );
	}

}


/*
 * Initialize tag-cloud
 **/
function tagcloud_init() {

	// make tag cloud
	requestWebservices( {"cmd" : "getTags", "args" : { "maxcount" : 25 } },
		function(data) {
			if (handleError(data)) {
				var tags_html = new Array();

				var max = null;
				for ( tag in data.tags ) {
					if (!max) {
						max = data.tags[tag];
					}
					tags_html.push('<a href="#cmd=search&filter=' 
						+ tag + '" onclick="doSearch( { \'cmd\' : \'getData\', \'args\' : { \'filter\' : \'' 
						+ tag + '\' } } ); return false;" style="font-size: ' 
						+ Math.ceil(12 * data.tags[tag] / max) + 'pt">' + tag + '</a>');
				}

				tags_html.sort();
				$('#tagcloud').html(tags_html.join(' '));
			}
		});

}


function autocomplete_init() {

	// make tag cloud
	requestWebservices( {"cmd" : "getTags", "args" : { "maxcount" : 350 } },
		function(data) {
			if (!handleError(data)) {
				return;
			}
			tags = [];
			for ( tag in data.tags ) {
				tags.push( tag );
			}
			tags.sort();
			$( "#search" ).autocomplete({ 'source' : tags, 'minLength' : 2 });
		});

}

/*
 * Initialization (load data for interface, set calender, ...)
 **/
function init() {
	
	$(window).bind( 'hashchange', onHashChange );

	// get start view
	if ( window.location.hash == '' ) {
		requestWebservices( {
				"cmd" : "getNews", 
				"args" : { 
					"count"          : Math.max( cfg.newscount, cfg.newreccount ),
					"mimetypefilter" : cfg.newsrecfilter 
				} 
			}, loadStartpage );
	}
	
	loadTemplate( 'loading.tpl' );
	loadTemplate( 'error.tpl' );

	// preload templates for preview
	loadTemplate( 'lecturerPreview.tpl'  );
	loadTemplate( 'lecturerPreview.tpl'  );
	loadTemplate( 'feedPreview.tpl'      );
	loadTemplate( 'recordingPreview.tpl' );
	loadTemplate( 'seriesPreview.tpl'    );

	$(window).trigger( 'hashchange' );
	autocomplete_init();

	// Include ThemeRoller
	//	jquitr = {}; jquitr.s = document.createElement('script'); jquitr.s.src = 'http://jqueryui.com/themeroller/developertool/developertool.js.php?a=b.js'; document.getElementsByTagName('head')[0].appendChild(jquitr.s);
}


/*******************************************************************************
 * Load welcome page with news, new video-recordings, ...
 ******************************************************************************/
function loadStartpage( data ) {
	if (handleError(data)) {
		var recordings = '';
		var series     = '';
		var count = 0;
		for (i in data.news) {
			count++;
			var n = data.news[i];
			// series
			if (count <= cfg.newscount) {
				// shorten description
				var desc = ( n.description.length < 120 ) 
					? n.description 
					: n.description.substr(0, 120) + '&hellip;';
				series += fillTemplate( tpl.home.series_update, 
					{ 'seriesname' : n.seriesname, 'desc' : desc, 'id' : n.series_id } );
			}

			// recordings
			if (count <= cfg.newreccount) {
				var player = '';
				var replaceData = n;

				replaceData.url = escape(replaceData.url);
				replaceData.preview_url = escape(replaceData.preview_url);
				replaceData.formatname = replaceData.formatname.replace( /[\W_]/g, '' );
				replaceData.img = getImageFromRecObj( n, 'template/' + cfg.tplName + '/' + cfg.stdVidPreImg );
				recordings += fillTemplate( tpl.home.new_recording, replaceData );

			}
		}
		loadTemplate( 'home.tpl', 
			{ 'new_recordings' : recordings, 'series_updates' : series },
			function( data ) { setContent( data ); calendar_init(); tagcloud_init(); } );

		// set counter
		$('#count_recordings').html( before_counter + data.news.count.recording + after_counter );
		$('#count_series').html(     before_counter + data.news.count.series    + after_counter );
		$('#count_lecturer').html(   before_counter + data.news.count.lecturer  + after_counter );
		$('#count_podcast').html(    before_counter + data.news.count.feed      + after_counter );
	}

}


function mapStudIPLecturerData( data ) {
	
	if ( data == '' ) {
		return {};
	}

	var key = [ 
		'fullname',      'lastname',     'firstname',          'titlefront',
		'titlerear',     'image-href',   'inst-name',          'inst-href',
		'email',         'room',         'phone',              'fax',
		'homepage-href', 'office-hours', 'research-interests', 'cv',
		'publications'
		];
	var val = data.split( '\r\n<!-- -------------------- -->\r\n' );
	var result = {};
	for ( var i = 0; i < key.length; i++ ) {
		result[ key[i] ] = val[i];
	}

	return result;

}


function getImageFromRecObj( o, stdimg ) {
	return  o.image_url ? o.image_url 
			: ( o.thumbnail_url ? o.thumbnail_url 
				: stdimg );
}


function replaceBy( node, type, url, preview_url ) {

	url         = unescape( url );
	preview_url = unescape( preview_url );

	$( node ).html( playerPlugin[ type ]( 'home', url, preview_url ) );

}


function requestStudIPLecturer( user, onSuccess, onError ) {

	if (!onError) {
		onError = function() {
			loadTemplate( 'error.tpl', 
				{ 'title' : 'Error...', 'msg' : 'Could not connect to webservice...' }, 
				setContent );
		};
	}

	$.ajax({
		type     : 'POST',
		url      : './func/requestStudIPLecturer.php',
		data	   : ( { 'user' : user } ),
		dataType : 'text',
		success  : onSuccess,
		error    : onError
	});
}


/**
 * Send request to webservice
 *
 * request     data to send to the lernfunk-webservice
 * onSuccess   function that is called if the request was successfull
 * onError     function that is called if an error occured
 **/
function requestWebservices(request, onSuccess, onError) {

	if (!onError) {
		onError = function() {
			loadTemplate( 'error.tpl', 
				{ 'title' : 'Error...', 'msg' : 'Could not connect to webservice...' }, 
				setContent );
		};
	}

	/* TODO: Combine the  results */
	for ( key in cfg.webservices ) {
		request.key = cfg.webservices[key].key;
		$.ajax({
			type     : 'POST',
			url      : './func/webserviceAccess.php',
			dataType : 'json',
			data	   : ( { 'url' : cfg.webservices[key].url, 'request' : $.toJSON(request) } ),
			success  : onSuccess,
			error    : onError
		});
	}
}


/**
 * Check result from lernfunk-webservice and handle error-messages
 *
 * returns   if no error occured
 **/
function handleError(data) {
	
	var errData = { 'title' : 'Error...' };
	if (data.type === 'undefined') {
		errData.msg = 'No datatype defined.';
		loadTemplate( 'error.tpl', errData, setContent );
		return false;
	}
	if (data.type == 'error') {
		if (data.errtype == 'sql_error')
			errData.msg = 'type: ' + data.errtype + '\nmessage: ' + data.errmsg + '\nquery: ' + data.sql_statement;
		else
			errData.msg = 'type: ' + data.errtype + '\nmessage: ' + data.errmsg;
		loadTemplate( 'error.tpl', errData, setContent );
		return false;
	}
	
	if (data.type == 'result') {
		return true;
	}
	
	errData.msg = 'I have no idea what is wrong. But something definitly is...';
	loadTemplate( 'error.tpl', errData, setContent );
	return false
}

/**
 * Insert default string into input if it's empty
 *
 * field         the input as JavaScript-object
 * default_str   the default string which is inserted if the input is empty
 **/
function leave_inp(field, default_str) {
	if (field.value == '')
		field.value = default_str;
}


/**
 * Clear HTML-input if value is the default string
 *
 * field         the input as JavaScript-object
 * default_str   the default string (clear input if this string in the value)
 **/
function clear_inp(field, default_str) {
	if (field.value == default_str)
	field.value = '';
}


/**
 * Compare two attributes of absolute equality
 *
 * returns   if the two attributes are absolute equal
 **/
function compareAttribute( att1, att2 ) {
	if ( !att1 && !att2 )
		return true;
	return att1 && att2 && (att1 === att2);
}


/**
 * Compare all attributes specified in att of the two objects obj1 and obj2
 *
 * obj1   First object which attributes should be compared
 * obj2   Second object which attributes should be compared
 * att    Array of attributes to compare
 **/
function compareAttributes( obj1, obj2, att ) {
	for (k in att) {
		if ( typeof( obj1[att[k]] ) !== typeof( obj2[att[k]] ) )
			return false;
		if ( (typeof( obj1[att[k]] ) !== 'undefined') && (obj1[att[k]] !== obj2[att[k]]) )
			return false;
	}
	return true;
}


/**
 * Check if an attribute is a string
 **/
function isStrAttr( attr ) {
	return typeof( attr ) === 'string';
}


function onHashChange( e ) {
	var params = $.deparam.fragment();

	if ( window.location.hash == '' && currentState.cmd ) {
		window.location.href = window.location.protocol + '//' + window.location.host + window.location.pathname;
	} else if ( params.cmd && params.cmd == 'search' ) {
		var oldSearch = isStrAttr( currentState.cmd ) && currentState.cmd == 'search';
		oldSearch = oldSearch && compareAttributes( currentState, params, [ 'filter', 'date', 'department' ] );

		// only page has changes 
		if ( oldSearch ) {
			// page in hash (different from current page) -> go to new page
			if ( params.page && ( currentState.page != params.page ) ) {
				goToPage( parseInt( params.page ), true );
			// no page in hash -> go to first page
			} else if ( ( !params.page && currentState.page )
				&& ( !isStrAttr( currentState.resultfilter ) || params.resultfilter == currentState.resultfilter ) ) {
				goToPage( 1, true );
			// current page was a special page (details), this is regular
			} else if ( isStrAttr( currentState.identifier ) && !isStrAttr( params.identifier ) 
				&& ( params.resultfilter == currentState.resultfilter ) ) { // !isStrAttr( currentState.resultfilter ) || 
				goToPage( (params.page ? parseInt( params.page ) : 1) , true );
			// new page is a special page (details)
			} else if ( isStrAttr( params.mediatype ) && isStrAttr( params.identifier ) ) {
				getDetails( params.mediatype, parseInt( params.identifier ), true );
			// filtered result
			} else if ( isStrAttr( params.resultfilter ) 
					&& ( !isStrAttr( currentState.resultfilter ) || params.resultfilter != currentState.resultfilter ) ) {
				filterResults( params.resultfilter, true );
			// go back from filtered result to unfiltered result
			} else if ( !isStrAttr( params.resultfilter ) && isStrAttr( currentState.resultfilter ) ) {
				filterResults( null, true );
				goToPage( (params.page ? parseInt( params.page ) : 1) , true );
			}
		}

		// new search
		if ( ! oldSearch ) {
			args = {}
			if ( params.department ) {
				args.department = params.department;
				$('#department_search').val( params.department );
			}
			if ( params.filter ) {
				args.filter = params.filter;
				$('#search').val( params.filter );
			}
			if ( params.date )
				args.date = params.date;
			doSearch( { 'cmd' : 'getData', 'args' : args }, params.page, true );
			args.page = params.page;
			args.cmd = 'search';
			params = args;
		}

	// load static page
	} else if ( params.cmd && params.cmd == 'static' && params.p ) {
		var url = params.p;
		if ( params.std ) {
			url = 'pages/' + url + '.inc.htm';
		}
		loadPage( params.title, url );

	// load details only
	} else if ( isStrAttr( params.mediatype ) && isStrAttr( params.identifier ) ) {
		getDetails( params.mediatype, parseInt( params.identifier ), true );
	}

	// save current state
	currentState = params;
}



function handleSearchResult( data, part, reqMediatype ) {
	if ( !(part && lastSearch) ) {
		lastSearch = {
				'count' : { 'all' : 0 },
				'data'  : []
			};
	}
	if (!part) {
		// Set link counter
		$('div.object_count').html( before_counter + '0' + after_counter );
	}

	if ( !part ) {
		var new_objects = {
			'series'     : [],
			'lecturer'   : [],
			'recordings' : [],
			'podcast'    : [],
			'other'      : []
		};
	}

	for ( var mediatype in data.data ) {
		var typecount = 0;
		if ( part && lastSearch.count && lastSearch.count[ mediatype ] ) {
			 typecount = lastSearch.count[ mediatype ];
		}
		if ( mediatype == 'recordings' ) {
			// sort and prepare data
			for (var id in data.data[mediatype]) {
				var obj = {};
				var r   = data.data[mediatype][id];
				obj.department = r.d  ? r.d  : [];
				obj.academy    = r.a  ? r.a  : {};
				obj.lecturer   = r.l  ? r.l  : {};
				obj.title      = r.t  ? r.t  : '';
				obj.desc       = r.de ? r.de : '';
				obj.date       = r.da ? r.da : '';
				obj.img        = r.i  ? r.i  : '';
				obj.duration   = r.du ? r.du : '';
				obj.format     = r.f  ? r.f  : '';
				obj.mimetype   = r.m  ? r.m  : '';
				obj.series     = r.s  ? r.s  : '';
				obj.series_id  = r.si ? r.si : '';
				obj.cou_id     = r.ci ? r.ci : id;
				obj.mediatype  = mediatype;
				obj.id         = id;
				if ( part ) {
					lastSearch.data.push( obj );
				} else {
					new_objects.recordings.push( obj );
				}
				typecount++;
			}

		} else if ( mediatype == 'lecturer' ) {
			for (var id in data.data[mediatype]) {
				var obj = {};
				var r   = data.data[mediatype][id];
				obj.department = r.d  ? r.d  : [];
				obj.academy    = r.a  ? r.a  : {};
				obj.email      = r.e  ? r.e  : '';
				obj.ac_title   = r.at ? r.at : '';
				obj.name       = r.n  ? r.n  : '';
				obj.firstname  = r.f  ? r.f  : '';
				obj.title      = obj.ac_title + ' ' + obj.firstname + ' ' + obj.name;
				obj.mediatype  = mediatype;
				obj.id         = id;
				if ( part ) {
					lastSearch.data.push( obj );
				} else {
					new_objects.lecturer.push( obj );
				}
				typecount++;
			}

		} else if ( mediatype == 'podcast' ) {
			for (var id in data.data[mediatype]) {
				var obj = {};
				var r   = data.data[mediatype][id];
				obj.department    = r.d  ? r.d  : [];
				obj.academy       = r.a  ? r.a  : {};
				obj.lecturer      = r.l  ? r.l  : {};
				obj.title         = r.t  ? r.t  : '';
				obj.url           = r.u  ? r.u  : '';
				obj.series_id     = r.s  ? r.s  : '';
				obj.feedtype_desc = r.ft ? r.ft : '';
				obj.img           = r.i  ? r.i  : '';
				obj.desc          = r.de ? r.de : '';
				obj.desc_sh       = r.ds ? r.ds : '';
				obj.term_id       = r.ti ? r.ti : '';
				obj.term_sh       = r.ts ? r.ts : '';
				obj.term          = r.tl ? r.tl : '';
				obj.mediatype     = mediatype;
				obj.id            = id;
				if ( part ) {
					lastSearch.data.push( obj );
				} else {
					new_objects.podcast.push( obj );
				}
				typecount++;
			}

		} else if ( mediatype == 'series' ) {
			for (var id in data.data[mediatype]) {
				var obj = {};
				var r   = data.data[mediatype][id];
				obj.department    = r.d  ? r.d  : [];
				obj.academy       = r.a  ? r.a  : {};
				obj.lecturer      = r.l  ? r.l  : {};
				obj.title         = r.t  ? r.t  : '';
				obj.term          = r.te ? r.te : '';
				obj.term_id       = r.ti ? r.ti : '';
				obj.desc          = r.de ? r.de : '';
				obj.desc_sh       = r.ds ? r.ds : '';
				obj.img           = r.i  ? r.i  : '';
				obj.mobjcount     = r.c  ? r.c  : '';
				obj.mediatype     = mediatype;
				obj.id            = id;
				if ( part ) {
					lastSearch.data.push( obj );
				} else {
					new_objects.series.push( obj );
				}
				typecount++;
			}

		} else {
			// sort and prepare data
			for (var id in data.data[mediatype]) {
				var obj = data.data[mediatype][id];
				obj.mediatype = mediatype;
				obj.id = id;
				if ( part ) {
					lastSearch.data.push( obj );
				} else {
					new_objects.other.push( obj );
				}
				typecount++;
			}
		}

		lastSearch.count[ mediatype ] = typecount;
		// set counter
		$('#count_' + mediatype).html( before_counter + typecount + after_counter );
	}
	var countall = 0;
	for (var i in lastSearch.count) {
		if ( i != 'all' ) {
			countall += lastSearch.count[i];
		}
	}
	lastSearch.count.all = countall;

	if ( !part ) {
		lastSearch.data = new_objects.series.concat( 
			new_objects.lecturer ).concat( 
			new_objects.recordings ).concat( 
			new_objects.podcast ).concat( 
			new_objects.other );
	}

	if (!reqMediatype || (reqMediatype == $.bbq.getState( 'resultfilter' ) ) ) {
		currData = lastSearch;
	}


	if ( reqMediatype ) {
		loading[ reqMediatype ] = false;
	} else {
		for (t in loading) {
			loading[t] = false;
		}
	}

	if (!reqMediatype || (reqMediatype == $.bbq.getState( 'resultfilter' ) ) ) {
		// Set page title
		$('#pagetitle').text('Suchergebnisse in allen Kategorien');
		// clear content div
		$('#content').html('');


		// insert objects as content
		count = 0;
		for ( id in lastSearch.data ) {
			count++;
			addObjectBlock(lastSearch.data[id].mediatype, lastSearch.data[id]);
			if (count >= cfg.objectsPerPage)
				break;
		}

		// initialize fancybox, pager, ...
		onContentLoaded();

		// set pager
		setPager(1);

		$(window).trigger( 'hashchange' );

		if ( reqMediatype ) {
			filterResults( mediatype, true );
		}

	}

}


function doSearch( request, page, hashIsSet) {
	doCleanUp();
	if ( !hashIsSet ) {
		var params = shallowCopy( request.args );
		params.cmd = 'search';
		$.bbq.pushState( params, 2 );
	}
	// $('#content').html(loadingHTML);
	loadTemplate( 'loading.tpl', null, 
		setContent,
		function(data) { $('#content').html('loading...'); }, 
		function(data) { $('#content').html('loading...'); } );

	for (t in loading) {
		loading[t] = true;
	}

	if ( $.bbq.getState( 'resultfilter' ) ) {

		request.args.mediatype = ['recordings'];
		requestWebservices( request,
			function(data) {
				if (handleError(data))
					handleSearchResult( data, true, 'recordings' );
			}, function(err) {  });

		request.args.mediatype = ['lecturer'];
		requestWebservices( request,
			function(data) {
				if (handleError(data))
					handleSearchResult( data, true, 'lecturer' );
			}, function(err) {  });

		request.args.mediatype = ['podcast'];
		requestWebservices( request,
			function(data) {
				if (handleError(data))
					handleSearchResult( data, true, 'podcast' );
			}, function(err) {  });

		request.args.mediatype = ['series'];
		requestWebservices( request,
			function(data) {
				if (handleError(data))
					handleSearchResult( data, true, 'series' );
			}, function(err) {  });

	} else {
		requestWebservices( request,
			function(data) {
				if (handleError(data))
					handleSearchResult( data, false );
			}, function(err) { /* TODO */ });
	}

}


function goToPage( page, locaIsSet ) {
	if (currData) {
		if (!locaIsSet)
			$.bbq.pushState( { 'page' : page } );
		setPager(page);
		$('#content').html('');
		count = 0;
		stop = page * cfg.objectsPerPage;
		start = stop - cfg.objectsPerPage;
		for ( id in currData.data ) {
			if (count >= start) {

				addObjectBlock( currData.data[id].mediatype, currData.data[id]);
			}
			count++;
			if (count >= stop)
				break;
		}
		onContentLoaded();
		window.scrollTo( 0, 0 );

		var resultFilter = $.bbq.getState( 'resultfilter' );
		if ( resultFilter == 'series' ) {
			$('#pagetitle').text( 'Suchergebnisse in der Kategorie Veranstaltungen' );
		} else if ( resultFilter == 'recordings' ) {
			$('#pagetitle').text( 'Suchergebnisse in der Kategorie Aufzeichnungen' );
		} else if ( resultFilter == 'lecturer' ) {
			$('#pagetitle').text( 'Suchergebnisse in der Kategorie Dozenten' );
		} else if ( resultFilter == 'podcast' ) {
			$('#pagetitle').text( 'Suchergebnisse in der Kategorie Podcasts' );
		} else {
			$('#pagetitle').text( 'Suchergebnisse in allen Kategorien' );
		}
		if ( resultFilter )
			$('#titlebox_bottom').show();
	}
}


function setPager(page) {
	// check if there are datasets on the current site
   if (currData) {
      $('div.pager').html('');
      pagecnt = Math.ceil(currData.count.all / cfg.objectsPerPage);
      if (pagecnt <= 1) {
			$('div.pager').css('display', 'none');
         return;
		}
		$('div.pager').css('display', 'block');
      start = Math.max(1, page - 2);
      stop  = Math.min(page + 2, pagecnt);
      if (page > 1) {
         $('div.pager').append('<div class="pagelink pagelink_prev" onclick="goToPage(' + (page - 1) + ')">&larr;</div>&nbsp;');
         if (page > 3) {
            $('div.pager').append('<div class="pagelink pagelink_1" onclick="goToPage(1)">1</div>&nbsp;');
            if (page > 4) {
               $('div.pager').append('<div class="pagedots">&hellip;</div>&nbsp;');
            }
         }
      }
      for (var i = start; i <= stop; i++) {
         $('div.pager').append('<div class="pagelink pagelink_' + i 
				+ '" onclick="goToPage(' + i + ')">' + i + '</div>&nbsp;');
      }
      if (page < pagecnt) {
         if (page < pagecnt - 2) {
            if (page < pagecnt - 3) {
               $('div.pager').append('<div class="pagedots">&hellip;</div>&nbsp;');
            }
            $('div.pager').append('<div class="pagelink pagelink_' + pagecnt + '" onclick="goToPage(' 
					+ pagecnt + ')">' + pagecnt + '</div>&nbsp;');
         }
         $('div.pager').append('<div class="pagelink pagelink_next" onclick="goToPage(' 
				+ (page + 1) + ')" id="nextpage" title="Nächste Seite">&rarr;</div>');
      }
      // $('#pagelink_' + page).css('background-color', '#ddeedd');
      $('.pagelink_' + page).addClass( 'pagelink_active' );
   }
}


function triggerSearch( std ) {
	if ( ($('#search').val() != '') && ($('#search').val() != std) ) {
		$.bbq.pushState( { 'filter' : $('#search').val(), 'cmd' : 'search' }, 2 );
	} else {
		$.bbq.pushState( { 'cmd' : 'search' }, 2 );
	}
}

function setFilterHash( m ) {
	if ($.bbq.getState( 'search' ) && $.bbq.getState( 'resultfilter' ) && $.bbq.getState( 'filter' )) {
		filterResults( m, true );
	} else {
		window.location.hash = '#cmd=search&resultfilter='  + m
			+ ($.bbq.getState( 'filter' ) ? '&filter=' + $.bbq.getState( 'filter' ) : '');
	}
}

function filterResults( mediatype, hashIsSet, subFilter ) {

	if (loading[ mediatype ]) {
		doCleanUp();
		loadTemplate( 'loading.tpl', null, 
			setContent,
			function(data) { $('#content').html('loading...'); }, 
			function(data) { $('#content').html('loading...'); } );
		return;
	}
	if (!lastSearch) {
		window.location.hash = '#resultfilter=' + mediatype + '&cmd=search';
		return;
	}

	$('body').removeClass( 'resultfilter_series resultfilter_recordings '
		+ 'resultfilter_lecturer resultfilter_podcast' )
		.addClass( 'resultfilter_' + mediatype );

	if ( !hashIsSet ) {
		alert( 'WARNING: Hash was not set in filterResults' );
	}

	$('#titlebox_bottom').html('');
	$('#content').html('');
	
	// create current data set
	currData = { 'count' : { 'all' : 0 }, 'data' : [] };
	var count = 0;
	for ( id in lastSearch.data ) {
		if (!mediatype || lastSearch.data[id].mediatype == mediatype) {
			var ok = true;
			// subfilter
			if (subFilter) {
				for (key in subFilter) {
					var o = lastSearch.data[id][key];
					if ( typeof(o) === 'string' ) {
						ok &= (o == subFilter[key]);
					} else if ( typeof(o) === 'object' ) {
						var tmp = false;
						for ( i in o ) {
							tmp |= ( o[i] == subFilter[key] );
						}
						ok &= tmp;
					}
				}
			}
			if (ok) {
				currData.data.push( lastSearch.data[id] );
				if (count < cfg.objectsPerPage) {
					addObjectBlock(mediatype, lastSearch.data[id]);
				}
				count++;
			}
		}
	}
	currData.count.all = count;
	currData.count[mediatype] = count;

	onResultFilter( mediatype );

	// initialize fancybox, pager, ...
	onContentLoaded();

	// set pager
	setPager(1);


}


function lexCompare(a, b) { 
	return a < b ? -1 : ( a > b ? 1 : 0); 
}


function appendContent( data ) {
	$('#content').append( data ); 
}


function appendContentSpace() {
	$('#content').append( ' ' ); 
}


function setContent( data ) {
	$('#content').html( data );
}


/********************************************************************************
 * Adds a content block to the main area of the page with short
 * information about the given object.
 *
 *  mediatype   The mediatype of the given object (video, slides, lecturer, 
 *              podcast, ...). This determines which information are displayed.
 *  object      The actual data of the object.
 ********************************************************************************/
function addObjectBlock(mediatype, obj) {
	if (!mediatype || !obj)
		return;


	// do this for all kinds of objects
	var replace = shallowCopy( obj );
	tplData     = shallowCopy( obj );
	if (!obj.img)
		obj.img = 'template/' + cfg.tplName + '/' + cfg.stdPreviewImg;
	replace.department = addDepartmentBlock(obj);

	//**************************************************************************
	//* Aufzeichnungen                                                        **
	//**************************************************************************
	if (mediatype == 'recordings') {
		replace.lecturer   = addLecturerBlock(obj);
		replace.series_rec_link = window.location.hash 
			+ '&mediatype=series&identifier=' + replace.series_id 
			+ '&couid=' + replace.cou_id + '&id=' + replace.id;
		loadTemplate( 'recordingPreview.tpl', replace, appendContent, null, appendContentSpace );

	//**************************************************************************
	//* Dozenten																				  **
	//**************************************************************************
	} else if  (mediatype == 'lecturer') {
		replace.academy    = addAcademyBlock(obj);
		if (replace.email) {
			var crypt = '';
			for (var i = 0; i < replace.email.length; i++)
				crypt += '&#' + replace.email.charCodeAt(i) + ';';
			replace.email = crypt;
		}
		loadTemplate( 'lecturerPreview.tpl', replace, appendContent, null, appendContentSpace );

	//**************************************************************************
	//* Podcast																					**
	//**************************************************************************
	} else if  (mediatype == 'podcast') {
		replace.lecturer   = addLecturerBlock(obj);
		loadTemplate( 'feedPreview.tpl', replace, appendContent, null, appendContentSpace );

	//**************************************************************************
	//* Vorlesungen																			  **
	//**************************************************************************
	} else if  (mediatype == 'series') {
		replace.lecturer   = addLecturerBlock(obj);
		if (!replace.mobjcount) {
			replace.mobjcount = 0;
		}
		loadTemplate( 'seriesPreview.tpl', replace, appendContent, null, appendContentSpace );
	}
}


function shallowCopy( obj ) {
	copy = {};
	for ( key in obj )
		if ( typeof( obj[ key ] ) != 'object' )
			copy[ key ] = obj[ key ];
	return copy;
}


function shallowCopyWithPointer( obj ) {
	copy = {};
	for ( key in obj )
		copy[ key ] = obj[ key ];
	return copy;
}


function addLecturerBlock(obj) {
	var tll = tpl.details.lecturerlink;
	var tlb = tpl.details.lecturerblock;
	if ( arguments.length == 3 ) {
		tll = arguments[1];
		tlb = arguments[2];
	}
	var lecturerblock = '';
	if (obj.lecturer) {
		var writeSeperator = false;
		for ( lid in obj.lecturer ) {
			if (writeSeperator)
				lecturerblock += ', \n';
			else
				writeSeperator = true;
			lecturerblock += fillTemplate( tll, 
					{ 'lecturer' : obj.lecturer[lid], 'lecturer_id' : lid } );
		}
	}
	if (lecturerblock)
		return fillTemplate( tlb, { 'lecturerlinks' : lecturerblock } );
	return '';
}


function addDepartmentBlock(obj) {
	var tl = tpl.details.departmentlink;
	var tb = tpl.details.departmentblock;
	if ( arguments.length == 3 ) {
		tl = arguments[1];
		tb = arguments[2];
	}
	var departmentblock = '';
	if (obj.department) {
		var writeSeperator = false;
		for ( department_id in obj.department ) {
			if (writeSeperator)
				departmentblock += ', \n';
			else
				writeSeperator = true;
			departmentblock += fillTemplate( tl, { 'department' : obj.department[department_id] } );
		}
	}
	if (departmentblock)
		return fillTemplate( tb, { 'departmentlinks' : departmentblock } );
	return '';
}


function addAcademyBlock(obj) {
	var tl = tpl.details.academylink;
	var tb = tpl.details.academyblock;
	if ( arguments.length == 3 ) {
		tl = arguments[1];
		tb = arguments[2];
	}
	var academyblock = '';
	if (obj.academy) {
		var writeSeperator = false;
		for ( ac_id in obj.academy ) {
			if (writeSeperator)
				academyblock += ', \n';
			else
				writeSeperator = true;
			academyblock += fillTemplate( tl, { 'academy' : obj.academy[ac_id] } );
		}
	}
	if (academyblock)
		return fillTemplate( tb, { 'academylinks' : academyblock } );
	return '';
}


function activateTab(tab_id) {
	$('div.titlebox_tab').removeClass( 'titlebox_tab_active' );
	$('#titlebox_tab_' + tab_id).addClass( 'titlebox_tab_active' );
}


function addTab(tab_id, name, func) {
	$('#titlebox_bottom').append('<div class="titlebox_tab" id="titlebox_tab_' + tab_id 
		+ '" onclick="activateTab(\'' + tab_id + '\'); ' + func + '">' + name + '</div>');
}


function sortResults(sorter) {
	if (!currData)
		return;
		
	// sort data
	var data = currData.data;
	data.sort(sorter);
	

	$('#content').html('');
	var count = 0;
	for ( id in data ) {
		addObjectBlock(data[id].mediatype, data[id]);
		count++;
		if (count >= cfg.objectsPerPage)
			break;
	}
	
	// initialize fancybox, pager, ...
	onContentLoaded();

	// set pager
	setPager(1);

}


function firstProp(obj) {
	if (obj)
		for (x in obj)
			return obj[x];	
	return null;
}


function firstPropId(obj) {
	if (obj)
		for (x in obj)
			return x;	
	return null;
}


function loadPage(title, page) {	
	doCleanUp();
	$.ajax({
		url: page,
		dataType: 'text',
		success: function(data) {
			$('#content').html(data);
			$('#pagetitle').text(title);		
		},
		error: function() {
			$('#content').html('<div style="text-align: center; padding: 50px;">Fehler: Seite konnte nicht geladen werden.</div>');
			$('#pagetitle').text(title);		
		}
	});
}


/**
 * Shows the detail page
 *   mediatype    the type of the dataset to show information about
 *   identifier   the identifier of the dataset
 *   hashIsSet    defines if the hash has to be set or not
 */
function getDetails( mediatype, identifier, hashIsSet ) {


	if ( !hashIsSet ) {
		$.bbq.pushState( { 'mediatype' : mediatype, 'identifier' : identifier } );
	}

	requestWebservices( { "cmd" : "getDetails", "args" : 
		{ "mediatype" : mediatype, "identifier" : identifier } },
		function(data) {
			if (!handleError(data)) {
				return; // some error occured
			}

			tplData = shallowCopyWithPointer( data );

			/**** LECTURER ***************************************************/
			if ( mediatype == 'lecturer' ) {
				setBackPager();
				data = data.details;
				data.academy = addAcademyBlock( data );
				data.department = addDepartmentBlock( data );
				data.series = makeSeriesTable( data.series );
				loadTemplate( 'lecturerDetails.tpl', data, setContent );
		
			/**** RECORDING **************************************************/
			} else if ( mediatype == 'recordings' ) {
				setBackPager();
				data = data.details;
				data.academy = addAcademyBlock( data );
				data.department = addDepartmentBlock( data );
				data.lecturer = addLecturerBlock( data );
				data.player = '';
				// if recordings is a set of slides
				if ( data.mimetype.match( /.*video.*/ ) ) {
				
					data.player = fillTemplate( tpl.details.videoplayer, { 'url' : data.url } );

				// if recording is virtpresenter recording
				} else if ( data.mimetype.match( /.*virtpresenter.*/ ) ) {
					data.player = fillTemplate( tpl.details.virtpresenterplayer, 
							{ 'url' : data.url } );

				// if recording is audio recording
				} else if ( data.mimetype.match( /.*audio.*/ ) ) {
					data.player = fillTemplate( tpl.details.audioplayer, 
							{ 'url' : data.url } );
				}
				loadTemplate( 'recordingDetails.tpl', data, setContent );

			/**** PODCAST ****************************************************/
			} else if ( mediatype == 'podcast' ) {
				setBackPager();
				var replace = {
						'url'          : data.details.url,
						'feedtype'     : data.details.feedtype,
						'term'         : data.details.term,
						'series_name'  : data.details.series.name,
						'series_desc'  : data.details.series.desc,
						'series_thumb' : data.details.series.thumb
					}
				loadTemplate( 'feedDetails.tpl', replace, setContent );
			/**** SERIES *****************************************************/
			} else if ( mediatype == 'series' ) {
				setBackPager();
				data = data.details;

				// shorten description
				if (!data.desc_sh) {
					data.desc_sh = data.desc.substr(0, 256) + '&hellip;';
				}
				// prepare recordings
				if (data.recordings) {
					var firstRecording = { 'result' : {} };

					for (var i in data.recordings) {
						if (!data.recordings[i].cou_id)
							data.recordings[i].cou_id = data.recordings[i].id;
					}

					data.recording_html = makeMediaobjectTable( data, 
							'recordings', firstRecording );
					data.firstrecording_title    = firstRecording.result.title;
					data.firstrecording_mimetype = firstRecording.result.mimetype;
					data.firstrecording_url      = firstRecording.result.url;
					data.firstrecording_cou_id   = firstRecording.result.cou_id;
				} else {
					data.recording_html = fillTemplate( tpl.seriesdetails.norecording, {} );
				}

				data.share_twitter = 'http://twitter.com/home?status=' + data.name 
					+ ' http://lernfunk.de/Main/' + data.portal_url;
				data.share_facebook = 'http://www.facebook.com/sharer.php?u='
					+ 'http://lernfunk.de/Main/' + data.portal_url;
				data.share_delicious = 'http://del.icio.us/post?url='
					+ 'http://lernfunk.de/Main/' + data.portal_url 
					+ '&amp;title=lernfunk.de: ' + data.name;

				data.academy    = $.toJSON( data.academy );
				data.department = $.toJSON( data.department );
				data.feeds      = $.toJSON( data.feeds );

				/* Format description */
				data.desc = '<p>' + data.desc.replace( /\n\n/g, '</p><p>' ) + '</p>';

				loadTemplate( 'seriesDetails.tpl', data, function( data ) {
							$('#content').html( data ).ready( function() {
								var p = $.deparam.fragment();
								if ( p.couid ) {
									loadRec( '#mediaobjectplayer', p.couid );
								}
							} );
						} );
			} else {
				// TODO 
			}
		},
		function(err) {
			// TODO
		});

}

/*******************************************************************************
 * Load recording select best format and call loadVideo
 ******************************************************************************/
function loadRec( target, couid ) {

	var p = $.deparam.fragment();
	var search_best = true;
	var format_links = '';
	var first = null;
	for ( i in seriesRecBuf[couid] ) {
		if (search_best) {
			if ( p.id && p.id == seriesRecBuf[couid][i].id ) {
				first = seriesRecBuf[couid][i];
				search_best = false;
			} else if ( !first ) {
				first = seriesRecBuf[couid][i];
			} else if ( seriesRecBuf[couid][i].mimetype.match(/.*matterhorn.*/) ) {
				first = seriesRecBuf[couid][i];
			} else if ( !first.mimetype.match(/.*matterhorn.*/) 
					&& seriesRecBuf[couid][i].mimetype.match(/.*video.*/) ) {
				first = seriesRecBuf[couid][i];
			}
		}
		format_links += fillTemplate( tpl.seriesdetails.rec_link, seriesRecBuf[couid][i] );
	}
	
	var rec          = shallowCopyWithPointer( first );
	rec.format_links = format_links;
	rec.playerid     = 'playerplaceholder';
	rec.portal_url   = tplData.portal_url;

	$( target ).html(  fillTemplate( tpl.seriesdetails.recordingplayerview, rec ) ).
		ready( function() { 
			loadVideo( '#playerplaceholder', couid, first.id ); 
		} );
}


/*******************************************************************************
 * Embed player into webpage
 ******************************************************************************/
function loadVideo( target, couid, id ) {

	var mimetype = '';
	var url      = '';
	var preview  = '';
	for (i in seriesRecBuf[couid]) {
		if (seriesRecBuf[couid][i].id == id) {
			format   = seriesRecBuf[couid][i].format.replace( /[\W_]/g, '' );
			url      = seriesRecBuf[couid][i].url;
			preview  = seriesRecBuf[couid][i].preview;
		}
	}

	if (!url)
		return;

	$( target ).html( playerPlugin[ format ]( 'seriesDetails', url, preview ) );

}


function setBackPager() {
	pStyleBuffer.push( $('div.pager').css( 'display' ) ); 
	pagerBuffer.push( $('div.pager').html() );
	contentBuffer.push( $('#content').html() );
	//$('div.pager').css('display', 'block');
	$('div.pager').css('display', 'none');
	$('div.pager').html( '<div class="pagelink" id="pagelink_back" onclick="history.back();">zur&uuml;ck</div>' );
}

/*******************************************************************************
 * Build a table of all recordings
 ******************************************************************************/
function makeMediaobjectTable( data, mediatype, firstRecordingObj ) {
	
	var firstRecording = null;
	var objects = '';
	var rel_rec = {};
	for ( i in data.recordings ) {
		var o = data.recordings[i];
		if (! rel_rec[ o.cou_id ])
			rel_rec[ o.cou_id ] = [];
		rel_rec[ o.cou_id ].push( o );
	}

	// buffer recordings for this page
	seriesRecBuf = rel_rec;

	var first = true;
	for ( cou_id in rel_rec ) {
		var link = '';
		var rec_data = [];
		var title = null;
		var format_links = '';
		for ( var i in rel_rec[cou_id] ) {

			/* get format links */
			format_links += fillTemplate( tpl.seriesdetails.rec_link, rel_rec[cou_id][i] );

			var recording = shallowCopy( rel_rec[cou_id][i] );
			recording.mediatype = mediatype;
			if ( first ) {
				if (!firstRecording) {
					firstRecording = shallowCopy( rel_rec[cou_id][i] );
				} else if ( recording.mimetype.match(/.*video.*/) ) {
					firstRecording = shallowCopy( rel_rec[cou_id][i] );
				}
			}
			rec_data.push( { 'mimetype':recording.mimetype, 'url':recording.url, 
					'format':recording.format, 'preview':recording.preview } );
			title = recording.title;
			link += fillTemplate( tpl.seriesdetails.rec_link, recording );
		}
		var recording = shallowCopy( rel_rec[cou_id][0] );
		recording.link = link;
		recording.format_links = format_links;
		recording.rec_data = $.toJSON( { 'title' : title, 'data' : rec_data } ).replace(/"/g, "'");
		recording.desc75 = (recording.desc.length <= 75) 
			? recording.desc 
			: recording.desc.substr(0, 72) + '...';
		// check image
		if (!recording.img) {
			recording.img = 'template/' + cfg.tplName + '/' + cfg.stdRecPreImg;
		}
		recording.portal_url = data.portal_url;
		objects += fillTemplate( tpl.seriesdetails.recording, recording );
		first = false;
	}
	if ( typeof(firstRecordingObj) == 'object' )
					firstRecordingObj.result = firstRecording;
	return objects;
}


/*******************************************************************************
 * Fill the seriestable with data (lecturer details page)
 ******************************************************************************/
function makeSeriesTable( data ) {
	series = '';
	term_series = {};
	term_ids    = [];
	for ( series_id in data ) {
		s = data[ series_id ];
		if ( ! term_series[ s.term_id ] ) {
			term = {};
			term.name = s.term;
			term.series = [ '<tr onclick="getDetails(\'series\', ' 
				+ series_id + ');" class="link"><td>' + s.name 
				+ '</td><td>' + s.desc + '</td></tr>' ];
			term_series[ s.term_id ] = term;
			term_ids.push( s.term_id );
		} else {
			term_series[ s.term_id ].series.push( '<tr onclick="getDetails(\'series\', ' 
				+ series_id + ');" class="link"><td>' 
				+ s.name + '</td><td>' + s.desc + '</td></tr>' );
		}
	}
	term_ids.sort();
	for ( i = term_ids.length - 1; i >= 0; i-- ) {
		term = term_series[ term_ids[ i ] ];
		series += '<tr><th colspan="2">' + term.name + '</th></tr>\n';
		series += term.series.join('\n');
	}
	return '<table>' + series + '</table>';
}


/*******************************************************************************
 * Fills template (given as string) with data
 ******************************************************************************/
function fillTemplate( template, replaceData ) {

	if ( typeof(replaceData) == 'undefined' )
		return template;

	var data = template;
	var all = '';
	for ( key in replaceData ) {
		all += '(:' + key + ':) ';
	}
	
	if (!replaceData)
		replaceData = {};
	replaceData.__all__ = all;

	for ( key in replaceData ) {
		var re = new RegExp( '\\(:' + key + ':\\)', 'g' );
		data = data.replace( re, replaceData[ key ] );
	}
	var re = new RegExp( '\\(:\\(.+?\\):\\(.*?\\):\\(.*?\\):\\(.*?\\):\\)', 'g' );
	var ma = data.match( re );
	for ( var i = 0; i < (ma ? ma.length : 0); i++ ) {
		var keywords = ma[i].split( '):(' );
		keywords[0] = keywords[0].split( '(:(' )[1];
		keywords[3] = keywords[3].split( '):)' )[0];
		if ( typeof( replaceData[ keywords[0] ] ) == 'string') {
			var re = '(:(' + keywords[0] + '):(' 
				+ keywords[1] + '):(' + keywords[2] + '):(' 
				+ keywords[3] + '):)';
			if (replaceData[ keywords[0] ] == keywords[1]) {
				data = data.replace( re, keywords[2] );
			} else {
				data = data.replace( re, keywords[3] );
			}
		}
	}

	return data;

}


/*******************************************************************************
 * Load a template from a file and replace the given keywords.
 * If a template is loaded once, it is stored in a local buffer.
 *
 *   template      : The file to load
 *   replaceData   : Key-value-pairs containing the data to replace
 *   onSuccess     : Function to execute once the replacements are done.
 *                   The result is passed as first argument
 *   onError       : Function for error handling
 *   onAJAXRequest : This function is executed right before an AJAX-request 
 *                   is made, thus only when onSuccess is executed asynchronous
 ******************************************************************************/
function loadTemplate( template, replaceData, onSuccess, onError, onAJAXRequest ) {
	if (! onError) {
		onError = function( err ) { 
			// TODO
		}
	}
	if ( templates[ template ] ) {
		var data = templates[ template ];
		if ( typeof(replaceData) != 'undefined' )
			data = fillTemplate( data, replaceData );
		if (onSuccess)
			onSuccess( data );
	} else {
		if (onAJAXRequest) {
			onAJAXRequest();
		}
		$.ajax({
			url: './template/' + cfg.tplName + '/' + template,
			dataType: 'text',
			data: {},
			success: function( data ) {
				templates[ template ] = data;
				if ( typeof(replaceData) != 'undefined' ) {
					data = fillTemplate( data, replaceData );
				}
				if (onSuccess)
					onSuccess( data );
			},
			error: onError
		});
	}
}


/*******************************************************************************
 * Delete all temporary data like old search results or pager
 * and hide the pager and tabs
 ******************************************************************************/
function doCleanUp() {
	// hide subfilter and show selectors
	$('#rightview_content').css( 'display', 'none'  );
	$('#rightview_select').css( 'display',  'block' );

	// hide pager
	$('div.pager').css( 'display', 'none' );
	// hide tabs, ...
	$('#titlebox_bottom').html('');
	// hide number of found objects
	//$('div.object_count').html('');
	// hide subfilter
	$('div.sub').css('display', 'none');

	lastSearch = null;
	currData = null;
	pagerBuffer   = [];
	contentBuffer = [];
	pStyleBuffer  = [];
}

/*******************************************************************************
 * Bind a player plugin to a format name.
 ******************************************************************************/
function bindPlayerPlugin( name, pluginFunction ) {

	name = name.replace( /[\W_]/g, '' );
	playerPlugin[ name ] = pluginFunction;

}

