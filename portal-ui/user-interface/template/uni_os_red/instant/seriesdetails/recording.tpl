<!-- one row of the recordings-table (details-page) -->
<table style="width: 100%; padding-left: 0px;">
	<tr class="onover_graybg recordingslistitem" style="width: 100%">
		<td style="width: 100%;" 
		onclick=" loadRec( '#mediaobjectplayer', '(:cou_id:)' ); 
			$('.recordingselected').removeClass('recordingselected'); 
			$( this ).addClass('recordingselected'); 
			$('.closeall').hide(); 
			$( this ).children( '.closeall' ).show();">
			<span style="font-size: 11px; font-weight: bold; padding: 0px 2px;">
				&#9658;&#8194;(:title:)&nbsp;<span style="font-size: smaller;">vom&nbsp;(:date:)</span>				
			</span>
			
      <div class="closeall" style="display:none; cursor:default;">
          <div class="recordinginfo">
            <div style="padding: 5px 0px; border-bottom:1px solid #cccccc;">(:format_links:)</div>
              <div style="padding-top:7px;">
                (:desc:)
              </div>
              <table style="padding-bottom: 10px;">
                <tr>
		              <td> Direktlink: </td>
		              <td> <input type="text" id="shortlink" value="http://lernfunk.de/Main/(:portal_url:)?stream_id=(:id:)" /> </td>
	              </tr>
              </table>
          </div>	         
      
      </div>
		</td>
	</tr>
</table>
