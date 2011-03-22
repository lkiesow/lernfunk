<!-- one row of the recordings-table (details-page) -->
<table style="width: 100%; padding-left: 0px;">
	<tr class="onover_graybg recordingslistitem" 
		style="width: 100%">
		<td style="padding-left: 8px; width: 100%" 
		onclick=" loadRec( '#mediaobjectplayer', '(:cou_id:)' ); 
			$('.recordingselected').removeClass('recordingselected'); 
			$( this ).addClass('recordingselected'); 
			$('.closeall').hide(); 
			$( this ).children( '.closeall' ).show();">
			<span style="font-size: 11px; font-weight: bold;">
				&#9658;&#8194;(:title:)&nbsp;<span style="font-size: smaller;">vom&nbsp;(:date:)</span>				
			</span>
			<div class="closeall" style="display:none; padding: 5px 0px;">
        (:format_links:)	   
      </div>
		</td>
	</tr>
</table>
