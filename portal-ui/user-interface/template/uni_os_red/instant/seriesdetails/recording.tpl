<!-- one row of the recordings-table (details-page) -->
<div style="padding: 3px; margin: 2px;" class="onover_graybg recordingslistitem"
			>
			<div style="font-size: 11px; font-weight: bold; padding: 0px 2px; line-height: 150%;" 
			onclick=" loadRec( '#mediaobjectplayer', '(:cou_id:)' ); 
			expandRecordingInfo( this.parentNode );">
				&#9658;&#8194;(:title:)&nbsp;
				<span style="font-size: smaller;">vom&nbsp;(:date:)</span>				
			</div>

			<div class="closeall" style="display:none; cursor:default;">
				<div class="recordinginfo">
					<div style="padding-top:7px;">
						(:desc:)
					</div>
					<div class="eplcontainer"><em>Online schauen:</em> (:format_links:)</div>
					<div class="dllcontainer"><em>Herunterladen:</em> (:format_links:)</div>
					<div><em>Direktlink:</em>
						<input type="text" class="reclink" 
							value="http://lernfunk.de/Main/(:portal_url:)?stream_id=(:id:)" />
					</div>	         
				</div>	         

			</div>
</div>
