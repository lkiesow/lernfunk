var t = tplData;

$('#pagetitle').text( t.name );
$('#titlebox_bottom').hide();

/* load feeds */
if (t.feeds) {
	$( '#feeds' ).show();
	for ( var url in t.feeds ) {
		$('#pdct').append('<a href="' + url + '">'
				+ '<img src="template/uni_os_red/img/rss.png" alt="rss" /> ' + t.feeds[url] + '</a> ');
	}
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
var cou_id = $.bbq.getState( 'couid' );
if (cou_id) {
	loadRec( '#mediaobjectplayer', cou_id );
} else {
	if ( tplData.details.firstrecording_cou_id ) {
		loadRec( '#mediaobjectplayer', tplData.details.firstrecording_cou_id );
		expandRecordingInfo( $( '.recordingslistitem' )[0] );
	} else {
		expandRecordingInfo( $( '.recordingslistitem' )[0] );
	}
}

/* Autoscrolling function for preview player */
$( window ).scroll(function () { 
		$( '#mediaobjectplayer' ).css( 'margin-top' , 
			Math.max( Math.min( 
				$( window ).scrollTop() - playerMinTop + 20, 
				$( '#seriesdetails_rightbox' ).height()
					- $( '.objcontainer' ).first().innerHeight() ), 0 ) + 'px' );
		/*
		if ( $( window ).scrollTop() - playerMinTop + 20  <  0 ) {
			$( '#mediaobjectplayer' ).css( 'position', 'static' ).css( 'width', '100%' );
		} else {
			$( '#mediaobjectplayer' )
				.css( 'width', $( '#mediaobjectplayer' ).width() + 'px' )
				.css( 'top', '20px' ).css( 'position' , 'fixed' );
		}
		*/
	} );

/* Adjust elements after the window was resized. */
$( window ).resize( function() { 
		if ( $( '#mediaobjectplayer' ).css( 'position' ) == 'fixed' ) {
			$( '#mediaobjectplayer' ).css( 'width', $( '.objcontainer' ).width() + 'px' );
		}
	});

var playerMinTop = $( '#mediaobjectplayer' ).offset().top;
var playerLeft   = $( '#mediaobjectplayer' ).offset().left;

/* Show enhanced player in lightbox. */
$( 'a.enhanced_player' ).fancybox({
	'width'         : '90%',
	'height'        : '90%',
	'autoScale'     : false,
	'transitionIn'  : 'none',
	'transitionOut' : 'none',
	'type'          : 'iframe'
});

/***************************  FUNCTIONS  *************************************/
function showHideSlide( hide, show ) {
	//$( hide ).slideUp( 'fast' );
	//$( show ).slideDown( 'fast' ) ;
	$( hide ).hide();
	$( show ).show();
}


/*******************************************************************************
 * Expand recordinginfo (Show description, links, …)
 ******************************************************************************/
function expandRecordingInfo( it ) {

	$( '.recordingselected' ).removeClass( 'recordingselected' ); 
	$( it ).addClass( 'recordingselected' );
	$( '.closeall' ).hide(); 
	$( it ).children( '.closeall' ).show(); 
	$( '.eplcontainer' ).children( '.dll' ).hide();
	$( '.eplcontainer' ).children( '.epl' ).parent().show();
	$( '.dllcontainer' ).children( '.dll' ).parent().show();
	$( '.dllcontainer' ).children( '.epl' ).hide();

}

