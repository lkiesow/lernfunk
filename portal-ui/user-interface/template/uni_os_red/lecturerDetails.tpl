<script type="text/javascript">
	$('#pagetitle').text( '(:name:)' );
	$('#titlebox_bottom').hide();
</script>
<script type="text/javascript">
	if ( tplData.details.lms_lecturer_id ) {
		requestStudIPLecturer( tplData.details.lms_lecturer_id, function( data ) {
			var lecturerdata = mapStudIPLecturerData( data );
			if ( lecturerdata['research-interests'] ) {
				$( '#lecinfo' ).append( '<h4>Forschungsinteressen</h4><p>' 
					+ lecturerdata['research-interests'] + '</p>' );
			}
			if ( lecturerdata['cv'] ) {
				$( '#lecinfo' ).append( '<h4>Lebenslauf</h4><p>' + lecturerdata['cv'] + '</p>' );
			}
			if ( lecturerdata['publications'] ) {
				$( '#lecinfo' ).append( '<h4>Publikationen</h4><p>' 
					+ lecturerdata['publications'] + '</p>' );
			}
			if ( lecturerdata['image-href'] ) {
				$( '#lecpic' ).html( '<img src="' + lecturerdata['image-href'] 
					+ '" alt="' + lecturerdata['fullname'] + '" />' );
			}
		},
		function() {} );
	}
</script>

<h3>
	(:ac_title:) (:firstname:) (:name:)
	<a style="float: right; margin-right: 10px; margin-top: 2px; font-size: smaller;" href="(:url:)">(:url:)</a>
</h3>
		
<div>
	<div class="objcontainer">
		<div style="margin-top: -7px; margin-left: 10px;">
			(:academy:)
			(:department:)
		</div>
		
		<table style="width: 100%;"><tr><td id="lecinfo"></td><td id="lecpic"></td></tr></table>

		<p> </p>
		<h4>Veranstaltungen</h4>
		<div class="infoblock">
			(:series:)
		</div>
	</div>
</div>
