<!-- one row of the recordings-table (details-page) -->
<table style="width: 97%; padding-left: 0px;">
	<tr class="onover_graybg recordingslistitem" 
		style="width: 100%">
		<td style="padding-left: 8px; width: 100%" 
		onclick=" loadRec( '#mediaobjectplayer', '(:cou_id:)' ); 
			$('.recordingselected').removeClass('recordingselected'); 
			$( this ).addClass('recordingselected'); 
			$('.closeall').hide(); 
			$( this ).children( '.closeall' ).show();">
			<span style="font-size: 11px; font-weight: bold;">
				▶ (:title:)&nbsp;<span style="font-size: smaller;">vom&nbsp;(:date:)</span>				
			</span>
			<div class="closeall" style="display:none;">
        <iframe src="http://www.facebook.com/plugins/like.php?href=http://lernfunk.de/Main/(:portal_url:)&amp;layout=button_count&amp;show_faces=false&amp;width=75&amp;action=like&amp;font=tahoma&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:75px; height:21px; float:right;" allowTransparency="true"></iframe>
			   (:format_links:)
      </div>
		</td>
	</tr>
</table>
