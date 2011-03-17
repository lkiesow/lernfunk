<script type="text/javascript" src="template/uni_os_red/seriesDetails.js" />

<div>
	<div style="padding-bottom: 15px; min-width: 700px;">
		<div id="academies"   style="display: none;">&rarr;</div> 
		<div id="departments" style="display: none;">&rarr;</div> 
		<div id="lecturer"    style="display: inline;"> </div> 
	</div>



	<div class="table" style="width: 100%;">
		<div class="tr">
			<div class="td" style="width:700px; vertical-align: top; padding: 0px 25px 0px 0px; ">

				<div class="objcontainer">
					<div id="mediaobjectplayer">
						<center><img style="max-height: 300px;" 
							src="(:(thumb):():(./template/uni_os_red/img/std_preview.jpg):():)(:thumb:)" /></center>
					</div>
				</div>

				<div>
					<h4 class="headline">Informationen zur Veranstaltung</h4>

					<div id="desc_sh" class="infoblock" style="padding: 7px 0px;">
						(:desc_sh:)...
						<div class="informationln" onclick="showHideSlide( '#desc_sh', '#desc_long' );">
							▾ ▾ ▾ Mehr Details ▾ ▾ ▾ </div>
					</div>

					<div id="desc_long" class="infoblock" style="display: none; padding: 7px 0px;">
						(:desc:)
						
						<table style="margin-top: 10px;">
							<tr>
								<td> Direktlink: </td>
								<td> <input type="text" id="shortlink" 
									value="http://lernfunk.de/Main/(:portal_url:)" /> </td>
							</tr>
							<tr>
								<td> Feeds: </td>
								<td id="pdct"> </td>
							</tr>
						</table>

						<div class="informationln" onclick="showHideSlide( '#desc_long', '#desc_sh' );">
							▴ ▴ ▴ Weniger Details ▴ ▴ ▴ </div>
					</div>
				</div>
			
			</div>
			<div style="width: 40%; min-width: 350px; vertical-align: top; 
				border-left: 1px solid #3E424A;">
				<h4 class="headline">Aufzeichnungen</h4>
				<div style="max-height: 400px; overflow-y: auto; margin-top: 5px;">
					(:recordings:)
				</div>
			</div>
		</div>
	</div>

<script type="text/javascript">
if (0) {
	/* load feeds */
	var feeds = (:feeds:);
	for (var i = 0; i < feeds.length; i++) {
		$('p#pdct').append('<a href="' + feeds[i].url + '">'
				+ '<img src="template/uni_os_red/img/rss.png" alt="rss" /> ' 
				+ feeds[i].type + '</a> ');
	}
	/* load academies */
	var ac = (:academy:);
	for ( aid in ac ) {
		$('#academies').css('display', 'inline').prepend( ac[aid] + ' ' );
	}
	/* load departments */
	var dep = (:department:);
	alert( $.toJSON( dep ) );
	for ( did in dep ) {
		$('#departments').css( 'display', 'inline' )
			.prepend( '<a href="#cmd=search&department=' + dep[did] + '">' 
					+ dep[did] + '</a> ' );
	}
	/* load first recording */
	loadRec( '#mediaobjectplayer', '(:firstrecording_cou_id:)' );
}
</script>
</div>
