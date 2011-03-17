var t = tplData;

$('#pagetitle').text( t.name );
$('#titlebox_bottom').hide();

/* load feeds */
var f = t.feeds ? t.feeds : {};
for ( var url in f ) {
	$('#pdct').append('<a href="' + url + '">'
			+ '<img src="template/uni_os_red/img/rss.png" alt="rss" /> ' + f[url] + '</a> ');
}

/* load academies */
var ac = tplData.academy ? tplData.academy : {};
for ( var id in ac ) {
	$('#academies').css('display', 'inline').prepend( ac[id] + ' ' );
}

/* load departments */
var dep = tplData.department;
for ( var id in dep ) {
	$('#departments').css( 'display', 'inline' )
		.prepend( '<a href="#cmd=search&department=' + dep[id] + '">' 
				+ dep[id] + '</a> ' );
}

/* load lecturer */
var l = tplData.lecturer;
for ( var id in l ) {
	$('#lecturer').append( '<a href="javascript: getDetails(\'lecturer\', ' + id + '); ">' 
			+ l[id] + '</a> ' );
}

/* load first recording */
loadRec( '#mediaobjectplayer', tplData.firstrecording_cou_id );


/***************************  FUNCTIONS  *************************************/
function showHideSlide( hide, show ) {
	$( hide ).slideUp( 'fast', function() { 
			$( show ).slideDown( 'fast' ) 
		} );
}
