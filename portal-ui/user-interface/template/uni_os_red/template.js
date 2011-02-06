/**
 * This function is called when the requested sets of data are filtered. For 
 * example only show series, ...
 *
 * \param  mediatype  mediatype for which it is filtered
 **/
function onResultFilter( mediatype ) {

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
		addTab('chrono_up', 'Semester (⬆)', 
			"sortResults(function(a, b) { return lexCompare(parseInt(a.term_id), parseInt(b.term_id)); })");
		addTab('chrono_down', 'Semester (⬇)', 
			"sortResults(function(a, b) { return lexCompare(parseInt(b.term_id), parseInt(a.term_id)); })");
	}

	// do initial sorting

	if ( mediatype == 'series' ) {
		activateTab('chrono_down'); 
		sortResults(function(a, b) { return lexCompare(parseInt(b.term_id), parseInt(a.term_id)); })
	}
	if ( mediatype == 'lecturer' ) {
		activateTab('alph_up'); 
		sortResults(function(a, b) { return lexCompare(a.name, b.name); })
	}
	if ( mediatype == 'podcast' ) {
		activateTab('chrono_down'); 
		sortResults(function(a, b) { return lexCompare(parseInt(b.term_id), parseInt(a.term_id)); })
	}

}


/**
 * Called after new content is loaded into the page
 **/
function onContentLoaded() {
	// Set error message if no data is available for the given request
	if ( !$('#content').html() )
			loadTemplate( 'error.tpl', 
				{ 'title' : 'Es tut uns leid...', 'msg' : 'Es konnten keine Daten '
					+ 'zu Ihrer Suchanfrage gefunden werden.' }, 
				setContent );
}