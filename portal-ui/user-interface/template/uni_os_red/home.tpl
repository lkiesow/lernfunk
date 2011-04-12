<div style="display: table; width: 100%;">
	<div style="display: table-row;">
		<div style="display: table-cell;">

			<div style="display: table; width: 100%;">
				<div style="display: table-row;">
					<div class="headline" style="display: table-cell; padding: 5px;">
						<b>Neue Aufzeichnungen</b>
					</div>
					<div class="headline" style="display: table-cell; padding: 5px; color: white;">
						<b>Zuletzt Aktualisiert</b>
					</div>
				</div>
				<div style="display: table-row; background-color: #fffcfa;">
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
				<div id="datepicker" style="font-size: smaller; margin-bottom: 25px;"></div>
				<div class="tagcloud">
        <h4 class="headline" style="padding: 4px;">Tag-Cloud</h4>
				<div id="tagcloud"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$( 'img.home_preview' ).bind('load', function() {
			$( this ).siblings( 'span.playbutton' ).offset( { 
					'left' : $( this ).offset().left + 100, 
					'top'  : $( this ).offset().top  + 40
				} );
		} )
</script>
