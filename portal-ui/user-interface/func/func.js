var dep_select_hide_interval = null;

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
				tags = new Array();
				var max = null;
				for ( tag in data.tags ) {
					if (!max)
						max = data.tags[tag];
					tags.push('<a href="#cmd=search&filter=' 
						+ tag + '" onclick="doSearch( { \'cmd\' : \'getData\', \'args\' : { \'filter\' : \'' 
						+ tag + '\' } } ); return false;" style="font-size: ' 
						+ Math.ceil(12 * data.tags[tag] / max) + 'pt">' + tag + '</a>');
				}
				tags.sort();
				$('#tagcloud').html(tags.join(' '));
			}
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
}


/**
 * Load welcome page with news, new video-recordings, ...
 **/
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
				delete( n.preview_url );
				delete( n.image_url );
				delete( n.seriesthumb );
				if ( n.mimetype.match( /.*video.*/ ) ) {
					replaceData.mediatype = 'Video';
					replaceData.img = getImageFromRecObj( n, 'template/' + cfg.tplName + '/' + cfg.stdVidPreImg );
					recordings += fillTemplate( tpl.home.new_recording, replaceData );
				} else if ( n.mimetype.match( /.*audio.*/ ) ) {
					replaceData.mediatype = 'Audio';
					replaceData.img = 'template/' + cfg.tplName + '/' + cfg.stdAudPreImg;
					recordings += fillTemplate( tpl.home.new_recording, replaceData );
				} else if ( n.mimetype.match( /.*virtpresenter.*/ ) ) {
					replaceData.mediatype = 'virtPresenter';
					replaceData.img = getImageFromRecObj( n, 'template/' + cfg.tplName + '/' + cfg.stdVidPreImg );
					recordings += fillTemplate( tpl.home.new_recording, replaceData );
				}
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


function getImageFromRecObj( o, stdimg ) {
	// do not use preview_url as it is often a link to the videofile
	// -> o.preview_url ? o.preview_url 
	return  o.image_url ? o.image_url 
			: ( o.thumbnail_url ? o.thumbnail_url 
				: stdimg );
}


function replaceBy( node, type, url ) {

	type = type.toLowerCase();
	if ( type == 'virtpresenter' || type == 'video' || type == 'audio' ) {
		
		// WARNING! 
		//   This is a UOS specific thing.
		//   And a dirty workaround!
		var rtmp = url.match( /^rtmp:\/\/[^&]+&url=.*$/ );
		if (rtmp) {
			rtmp = rtmp[0].split( '&' );
			url = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
		}

		$(node).html( fillTemplate( tpl.home[type + 'player'], { 'url' : url } ) );
	}

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
 * Simply load/get a webpage
 *
 * url         webpage to get
 * onSuccess   function that is called if the request was successfull
 * onError     function that is called if an error occured
 **/
function getWebpage( url, onSuccess, onError ) {
	// TODO
	alert( 'we still need getWebpage' );

	if (!onError) {
		onError = function() {};
	}

	$.ajax({
		type: 'POST',
		url: './func/getWebpage.php',
		dataType: 'plain',
		data	: ({ 'url' : url }),
		success: onSuccess,
		error: onError
	});
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
			} else if ( isStrAttr( currentState.details ) && !isStrAttr( params.details ) 
				&& ( !isStrAttr( currentState.resultfilter ) || params.resultfilter == currentState.resultfilter ) ) {
				goToPage( (params.page ? parseInt( params.page ) : 1) , true );
			// new page is a special page (details)
			} else if ( isStrAttr( params.details ) && isStrAttr( params.mediatype ) && isStrAttr( params.identifier ) ) {
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
				lastSearch.data.push( obj );
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
				lastSearch.data.push( obj );
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
				obj.mediatype     = mediatype;
				obj.id            = id;
				lastSearch.data.push( obj );
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
				lastSearch.data.push( obj );
				typecount++;
			}

		} else {
			// sort and prepare data
			for (var id in data.data[mediatype]) {
				var obj = data.data[mediatype][id];
				obj.mediatype = mediatype;
				obj.id = id;
				lastSearch.data.push( obj );
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
	currData = lastSearch;


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

		//if we want a specific page
//		if ( page )
//			goToPage( parseInt( page ) );

		$(window).trigger( 'hashchange' );
		filterResults( mediatype, true );

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


function onContentLoaded() {
	if ( !$('#content').html() )
			loadTemplate( 'error.tpl', 
				{ 'title' : 'Es tut uns leid...', 'msg' : 'Es konnten keine Daten zu Ihrer Suchanfrage gefunden werden.' }, 
				setContent );
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


function triggerSearch() {
	var request = { 'cmd' : 'getData', 'args' : {} };
	if ( ($('#search').val() != '') && ($('#search').val() != 'Suche') )
		request.args.filter = $('#search').val();

	if ( $('#search').val() ) {
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

	$('#titlebox').attr( 'class', 'resultfilter_' + mediatype );

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

	// set tabs for sorting
	if (mediatype == 'recordings') {
		$('#pagetitle').text('Suchergebnisse in der Kategorie Vorlesungsaufzeichnungen');
		addTab('alph', 'Alphabetisch',    "sortResults(function(a, b) { return lexCompare(a.title, b.title); })");
		addTab('chrono', 'Chronologisch', "sortResults(function(a, b) { return lexCompare(b.date, a.date); })");
		addTab('series', 'Veranstaltung', "sortResults(function(a, b) { return lexCompare(a.series, b.series); })");					
	} else if (mediatype == 'series') {
		$('#pagetitle').text('Suchergebnisse in der Kategorie Vorlesungen');
		addTab('alph', 'Alphabetisch', 
			"sortResults(function(a, b) { return lexCompare(a.title, b.title); })");
		addTab('chrono_up', 'Semester (⬆)', 
			"sortResults(function(a, b) { return lexCompare(parseInt(a.term_id), parseInt(b.term_id)); })");
		addTab('chrono_down', 'Semester (⬇)', 
			"sortResults(function(a, b) { return lexCompare(parseInt(b.term_id), parseInt(a.term_id)); })");

	} else if (mediatype == 'lecturer') {
		$('#pagetitle').text('Suchergebnisse in der Kategorie Personen');
		addTab('alph_up', 'Alphabetisch (⬆)', 
			"sortResults(function(a, b) { return lexCompare(a.name, b.name); })");
		addTab('alph_down', 'Alphabetisch (⬇)', 
			"sortResults(function(a, b) { return lexCompare(b.name, a.name); })");
		addTab('academy', 'Akademie',
			"sortResults(function(a, b) { return lexCompare(firstProp(a.academy), firstProp(b.academy)); })");
		addTab('series', 'Fachbereich', 
			"sortResults(function(a, b) { return lexCompare(firstProp(a.department), firstProp(b.department)); })");	

	} else if (mediatype == 'podcast') {
		$('#pagetitle').text('Suchergebnisse in der Kategorie Podcasts');
		addTab('alph_up', 'Alphabetisch (⬆)', 
			"sortResults(function(a, b) { return lexCompare(a.title, b.title); })");
		addTab('alph_down', 'Alphabetisch (⬇)', 
			"sortResults(function(a, b) { return lexCompare(b.title, a.title); })");
	}

	// initialize fancybox, pager, ...
	onContentLoaded();

	if ( mediatype == 'series' ) {
		activateTab('chrono_down'); 
		sortResults(function(a, b) { return lexCompare(parseInt(b.term_id), parseInt(a.term_id)); })
	}
	if ( mediatype == 'lecturer' ) {
		activateTab('alph_up'); 
		sortResults(function(a, b) { return lexCompare(a.name, b.name); })
	}
	if ( mediatype == 'podcast' ) {
		activateTab('alph_up'); 
		sortResults(function(a, b) { return lexCompare(a.title, b.title); })
	}

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


/**
 * Adds a content block to the main area of the page with short
 * information about the given object.
 *
 *  mediatype   The mediatype of the given object (video, slides, lecturer, 
 *              podcast, ...). This determines which information are displayed.
 *  object      The actual data of the object.
 **/
function addObjectBlock(mediatype, obj) {
	if (!mediatype || !obj)
		return;


	// do this for all kinds of objects
	var replace = shallowCopy( obj );
	if (!obj.img)
		obj.img = 'template/' + cfg.tplName + '/' + cfg.stdPreviewImg;
	replace.department = addDepartmentBlock(obj);

	//**************************************************************************
	//* Aufzeichnungen                                                        **
	//**************************************************************************
	if (mediatype == 'recordings') {
		replace.lecturer   = addLecturerBlock(obj);
		replace.series_rec_link = window.location.hash 
			+ '&details=1&mediatype=series&identifier=' + replace.series_id 
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


function showSubfilter( filter_by, mediatype ) {
	// TODO
	alert( 'we still need showSubfilter' );
	$('div.rightbox_tab').removeClass('rightbox_tab_active');
	$('#rightbox_tab_' + filter_by ).addClass('rightbox_tab_active');
	var a = [];
	var title = '';
	if ( filter_by == 'format' ) {
		var e = {};
		var data = lastSearch.data;
		for (i in data) {
			if ( data[i].mediatype == mediatype ) {
				if (!e[data[i].format]) {
					e[data[i].format] = true;
					a.push( data[i].format );
				}
			}
		}
	} else if ( filter_by == 'type' ) {
		var e = {};
		var data = lastSearch.data;
		for (i in data) {
			if ( data[i].mediatype == mediatype ) {
				if (!e[data[i].type]) {
					e[data[i].type] = true;
					a.push( data[i].type );
				}
			}
		}
	} else if ( filter_by == 'department' && lastSearch ) {
		var e = {};
		var data = lastSearch.data;
		for (i in data) {
			if ( data[i].mediatype == mediatype ) {
				for (j in data[i].department) {
					if (data[i].department[j] && !e[j]) {
						e[j] = data[i].department[j];
						a.push( data[i].department[j] );
					}
				}
			}
		}
	} else if ( filter_by == 'lecturer' && lastSearch ) {
		var e = {};
		var data = lastSearch.data;
		for (i in data) {
			if ( data[i].mediatype == mediatype ) {
				for (j in data[i].lecturer) {
					if (!e[j]) {
						e[j] = data[i].lecturer[j];
						a.push( data[i].lecturer[j] );
					}
				}
			}
		}
	}
	a.sort();

	var selection = '';
	for (i in a) {


		selection += '<div class="filter_select" onclick="filterResults( \'' 
			+ mediatype + '\', null, { \'' + filter_by + '\' : \'' + a[i] + '\' } );">' + a[i] + '</div> ';
	}
	$('#rightview_filter').html( selection );
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


function toLastSavepoint() {
	// TODO
	alert( 'we still need toLastSavepoint' );
	$('div.pager').html( pagerBuffer[ pagerBuffer.length - 1 ] );
	$('#content').html( contentBuffer[ contentBuffer.length - 1 ] );
	$('div.pager').css( 'display', pStyleBuffer ); 
	pagerBuffer.pop();
	contentBuffer.pop();
	pStyleBuffer.pop();
}


/**
 * Shows the detail page
 *   mediatype    the type of the dataset to show information about
 *   identifier   the identifier of the dataset
 *   hashIsSet    defines if the hash has to be set or not
 */
function getDetails( mediatype, identifier, hashIsSet ) {

	if ( !hashIsSet ) {
		var dc = $.bbq.getState( 'details' );
		if ( typeof(dc === 'undefined') )
			dc = 1;
		$.bbq.pushState( { 'details' : dc, 'mediatype' : mediatype, 'identifier' : identifier } );
	}

	requestWebservices( { "cmd" : "getDetails", "args" : { "mediatype" : mediatype, "identifier" : identifier } },
		function(data) {
			if (handleError(data)) {

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
					if ( data.mimetype == 'slides' ) {
						data.player = '<div id="slideplayer_container" '
							+ 'style="width: 600px; height: 304px; overflow-y: hidden; overflow-x: scroll;">'
							+ '<div id="slideplayer" style="max-height: 300px;"></div></div>';
					// if recording is a video
					} else if ( data.mimetype.match( /.*video.*/ ) ) {
					
						// WARNING! 
						//   This is a UOS specific thing.
						//   And a dirty workaround!
						var rtmp = data.url.match( /^rtmp:\/\/[^&]+&url=.*$/ );
						if (rtmp) {
							rtmp = rtmp[0].split( '&' );
							data.url = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
						}
						data.player  = '<p style="text-align: center;">';
						data.player += fillTemplate( tpl.details.videoplayer, { 'url' : data.url } );
						data.player += '</p>';

					// if recording is virtpresenter recording
					} else if ( data.mimetype.match( /.*virtpresenter.*/ ) ) {
						data.player = '<iframe src="' + data.url + '" style="width: 600px; height: 400px; border: none;"></iframe>'
							+ '<p style="text-align: right;"><a href="' + data.url + '">Standalone-Player</a></p>';

					// if recording is audio recording
					} else if ( data.mimetype.match( /.*audio.*/ ) ) {
						data.player  = '<p style="text-align: center;">';
						data.player += fillTemplate( tpl.details.audioplayer, { 'url' : data.url } );
						data.player += '</p>';
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
					data.lecturer   = addLecturerBlock( data, 
							tpl.seriesdetails.info.lecturerlink, 
							tpl.seriesdetails.info.lecturerblock );
					data.academy    = addAcademyBlock( data,
							tpl.seriesdetails.info.academylink, 
							tpl.seriesdetails.info.academyblock );
					data.department = addDepartmentBlock( data,
							tpl.seriesdetails.info.departmentlink, 
							tpl.seriesdetails.info.departmentblock );
					var firstRecording = { 'result' : {} };
					for (var i in data.recordings) {
						data.recordings[i].cou_id = data.recordings[i].id;
					}
					data.recordings = makeMediaobjectTable( data.recordings, 'recordings', firstRecording );
					data.firstrecording_title    = firstRecording.result.title;
					data.firstrecording_mimetype = firstRecording.result.mimetype;
					data.firstrecording_url      = firstRecording.result.url;

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
			}
		},
		function(err) {
			// TODO
		});

}

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
			} else if ( seriesRecBuf[couid][i].mimetype.match(/.*video.*/) ) {
				first = seriesRecBuf[couid][i];
			}
		}
		format_links += fillTemplate( tpl.seriesdetails.rec_link, seriesRecBuf[couid][i] );
	}
	$( target ).html(  fillTemplate( tpl.seriesdetails.recordingplayerview, { 
			'title'        : first.title, 
			'format_links' : format_links, 
			'playerid'     : 'playerplaceholder' 
		} ) ).ready( function() { 
			loadVideo( '#playerplaceholder', couid, first.id ); 
		} );
}


function loadRecording( target, recording ) {
	var format_links = '';
	var first = null;
	for ( i in recording.data ) {
		if ( !first ) {
			first = recording.data[i];
		} else if ( recording.data[i].mimetype.match(/.*video.*/) ) {
			first = recording.data[i];
		}
		format_links += fillTemplate( tpl.seriesdetails.rec_link, recording.data[i] );
	}
	$( target ).html(  fillTemplate( tpl.seriesdetails.recordingplayerview, { 
			'title'        : recording.title, 
			'format_links' : format_links, 
			'playerid'     : 'playerplaceholder' 
		} ) ).ready( function() { 
			loadPlayer( '#playerplaceholder', first.mimetype, first.url, first.preview ); 
		} );
}


function loadVideo( target, couid, id ) {

	var mimetype = '';
	var url      = '';
	var preview  = '';
	for (i in seriesRecBuf[couid]) {
		if (seriesRecBuf[couid][i].id == id) {
			mimetype = seriesRecBuf[couid][i].mimetype;
			url      = seriesRecBuf[couid][i].url;
			preview  = seriesRecBuf[couid][i].preview;
		}
	}

	if (!url)
		return;

	var player   = '';
	if ( mimetype.match( /.*video.*/ ) ) {
	
		// WARNING! 
		//   This is a UOS specific thing.
		//   And a dirty workaround!
		var rtmp = url.match( /^rtmp:\/\/[^&]+&url=.*$/ );
		if (rtmp) {
			rtmp = rtmp[0].split( '&' );
			url = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
		}
		player += '<p style="text-align: center;">';
		player += fillTemplate( tpl.details.videoplayer, { 'url' : url } );
		player += '</p>';

	} else if ( mimetype.match( /.*virtpresenter.*/ ) ) {
		if (preview) {
			// WARNING! 
			//   This is a UOS specific thing.
			//   And a dirty workaround!
			var rtmp = preview.match( /^rtmp:\/\/[^&]+&url=.*$/ );
			if (rtmp) {
				rtmp = rtmp[0].split( '&' );
				preview = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
			}
			player += '<p style="text-align: center;">';
			player += fillTemplate( tpl.details.videoplayer, { 'url' : preview } );
			player += '</p>';
			player += fillTemplate( tpl.details.standalonelink, { 'url' : url } );
		} else {
			player += fillTemplate( tpl.details.virtpresenterplayer, { 'url' : url } );
		}

	} else if ( mimetype.match( /.*matterhorn.*/ ) ) {
		if (preview) {
			// WARNING! 
			//   This is a UOS specific thing.
			//   And a dirty workaround!
			//   Matterhorn => Embed-Code in Preview-URL
			player += '<p style="text-align: center;">';
			player += preview;
			player += '</p>';
			player += fillTemplate( tpl.details.standalonelink, { 'url' : url } );
		} else {
			player += fillTemplate( tpl.details.virtpresenterplayer, { 'url' : url } );
		}

	} else if ( mimetype.match( /.*audio.*/ ) ) {
		player += '<p style="text-align: center;">';
		player += fillTemplate( tpl.details.audioplayer, { 'url' : url } );
		player += '</p>';
	}

	$( target ).html( player );

}


function loadPlayer( target, mimetype, url, preview ) {

	var player = '';
	if ( mimetype.match( /.*video.*/ ) ) {
	
		// WARNING! 
		//   This is a UOS specific thing.
		//   And a dirty workaround!
		var rtmp = url.match( /^rtmp:\/\/[^&]+&url=.*$/ );
		if (rtmp) {
			rtmp = rtmp[0].split( '&' );
			url = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
		}
		player += '<p style="text-align: center;">';
		player += fillTemplate( tpl.details.videoplayer, { 'url' : url } );
		player += '</p>';

	} else if ( mimetype.match( /.*virtpresenter.*/ ) ) {
		if (preview) {
			// WARNING! 
			//   This is a UOS specific thing.
			//   And a dirty workaround!
			var rtmp = preview.match( /^rtmp:\/\/[^&]+&url=.*$/ );
			if (rtmp) {
				rtmp = rtmp[0].split( '&' );
				preview = rtmp[1].slice( 4 ) + '&amp;streamer=' + rtmp[0];
			}
			player += '<p style="text-align: center;">';
			player += fillTemplate( tpl.details.videoplayer, { 'url' : preview } );
			player += '</p>';
			player += fillTemplate( tpl.details.standalonelink, { 'url' : url } );
		} else {
			player += fillTemplate( tpl.details.virtpresenterplayer, { 'url' : url } );
		}

	} else if ( mimetype.match( /.*matterhorn.*/ ) ) {
		if (preview) {
			// WARNING! 
			//   This is a UOS specific thing.
			//   And a dirty workaround!
			//   Matterhorn => Embed-Code in Preview-URL
			player += '<p style="text-align: center;">';
			player += preview;
			player += '</p>';
			player += fillTemplate( tpl.details.standalonelink, { 'url' : url } );
		} else {
			player += fillTemplate( tpl.details.virtpresenterplayer, { 'url' : url } );
		}

	} else if ( mimetype.match( /.*audio.*/ ) ) {
		player += '<p style="text-align: center;">';
		player += fillTemplate( tpl.details.audioplayer, { 'url' : url } );
		player += '</p>';
	}

	$( target ).html( player );

}


function setBackPager() {
	pStyleBuffer.push( $('div.pager').css( 'display' ) ); 
	pagerBuffer.push( $('div.pager').html() );
	contentBuffer.push( $('#content').html() );
	//$('div.pager').css('display', 'block');
	$('div.pager').css('display', 'none');
	$('div.pager').html( '<div class="pagelink" id="pagelink_back" onclick="history.back();">zur&uuml;ck</div>' );
}


function makeMediaobjectTable( data, mediatype, firstRecordingObj ) {
	
	var firstRecording = null;
	var objects = '';
	var rel_rec = {};
	for ( i in data ) {
		var o = data[i];
		if (! rel_rec[ o.cou_id ])
			rel_rec[ o.cou_id ] = [];
		rel_rec[ o.cou_id ].push( o );
	}
	// rel_rec.sort( function(a, b) { return lexCompare(b.date, a.date); } );

	// buffer recordings for this page
	seriesRecBuf = rel_rec;

	var first = true;
	for ( cou_id in rel_rec ) {
		var link = '';
		var rec_data = [];
		var title = null;
		for ( i in rel_rec[cou_id] ) {
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
		recording.rec_data = $.toJSON( { 'title' : title, 'data' : rec_data } ).replace(/"/g, "'");
		recording.desc75 = (recording.desc.length <= 75) 
			? recording.desc 
			: recording.desc.substr(0, 72) + '...';
		// check image
		if (!recording.img) {
			recording.img = 'template/' + cfg.tplName + '/' + cfg.stdRecPreImg;
		}
		objects += fillTemplate( tpl.seriesdetails.recording, recording );
		first = false;
	}
	if ( typeof(firstRecordingObj) == 'object' )
					firstRecordingObj.result = firstRecording;
	return objects;
}


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
	for ( i in ma ) {
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


/**
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
 **/           
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


function showSubmenu( menu_item ) {
	// TODO
	alert( 'we still need showSubmenu' );
	var subfilter = '';
	if ( menu_item == 'recordings' || menu_item == 'slides' ) {
		subfilter += makeSubmenuTab( 'format',     menu_item, 'Format'      );
		subfilter += makeSubmenuTab( 'department', menu_item, 'Fachbereich' );
		subfilter += makeSubmenuTab( 'lecturer',   menu_item, 'Dozent'      );
	} else if ( menu_item == 'series' ) {
		subfilter += makeSubmenuTab( 'department', menu_item, 'Fachbereich' );
		subfilter += makeSubmenuTab( 'lecturer',   menu_item, 'Dozent'      );
	} else if ( menu_item == 'lecturer' ) {
		subfilter += makeSubmenuTab( 'department', menu_item, 'Fachbereich' );
	} else if ( menu_item == 'podcast' ) {
		subfilter += makeSubmenuTab( 'type',       menu_item, 'Type'    );
		subfilter += makeSubmenuTab( 'department', menu_item, 'Fachbereich' );
		subfilter += makeSubmenuTab( 'lecturer',   menu_item, 'Dozent'      );
	}
	$('#rightbox_tabs').html( subfilter );
	showSubfilter( 'department', menu_item );
	$('#rightview_content').css( 'display', 'block' );
	$('#rightview_select' ).css( 'display', 'none'  );
}


/**
 * Create a string for a subfilter-tab
 *
 * filter_by   attribute to filter
 * mediatype   mediatype of the primary filter
 * label       the label of the tab
 **/
function makeSubmenuTab( filter_by, mediatype, label ) {
	// TODO
	alert( 'we still need makeSubmenuTab' );
	return '<div class="rightbox_tab" id="rightbox_tab_' + filter_by + '" onclick=" showSubfilter( \'' 
		+ filter_by + '\', \'' + mediatype + '\' ); ">' + label + '</div>';
}


/**
 * Delete all temporary data like old search results or pager
 * and hide the pager and tabs
 **/
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
