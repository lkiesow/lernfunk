<div style="display: table; width: 100%;">
	<div style="display: table-row;">
		<div style="display: table-cell;">

			<div style="display: table; width: 100%;">
				<div style="display: table-row;">
					<div style="display: table-cell; background-color: #485163; padding: 5px; color: white; font-weight: bold;">
						Neue Aufzeichnungen
					</div>
					<div style="display: table-cell; background-color: #485163; padding: 5px; color: white; font-weight: bold;">
						Zuletzt Aktualisiert
					</div>
				</div>
				<div style="display: table-row; background-color: #fcfcfc;">
					<div style="display: table-cell; ; max-width: 440px;">
						(:new_recordings:)
					</div>
					<div style="display: table-cell; max-width: 300px;">
						(:series_updates:)
					</div>
				</div>
			</div>
		</div>
		<div style="display: table-cell; background-color: white; width: 200px; padding-left: 15px;">
			<div id="rcbx" style="display: block;">
				<h2>Kalender</h2>
				<div id="datepicker" style="font-size: smaller; margin-bottom: 25px;"></div>
				<h2>Tag-Cloud</h2>
				<div id="tagcloud"></div>
			</div>
		</div>
	</div>
</div>
<script>
	$( 'img.home_preview' ).each( function( index, elem ) {
			$( this ).siblings( 'span' ).offset( { 
					'left' : $( this ).offset().left + 100, 
					'top'  : $( this ).offset().top  + 40
				} );
		} )
</script>
