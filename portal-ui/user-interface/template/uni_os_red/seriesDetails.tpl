<script type="text/javascript" src="template/uni_os_red/seriesDetails.js" />

<div>
	<div style="padding-bottom: 15px; min-width: 700px;">
		<div id="academies"   style="display: none;">&rarr;</div> 
		<div id="departments" style="display: none;">&rarr;</div> 
		<div id="lecturer"    style="display: inline;"> </div> 
	</div>

	<div class="table" style="width: 100%;">
		<div class="tr">
			<div class="td" id="seriesdetails_leftbox">

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

					<div id="desc_long" class="infoblock hidden" style="padding: 7px 0px;">
						(:desc:)
						
						<table style="margin-top: 10px;">
							<tr>
								<td> Direktlink: </td>
								<td> <input type="text" id="shortlink" 
									value="http://lernfunk.de/Main/(:portal_url:)" /> </td>
							</tr>
							<tr id="feeds" class="hidden">
								<td> Feeds: </td>
								<td id="pdct"> </td>
							</tr>
							<tr id="share">
								<td> Share </td>
								<td> 
									<a href="http://twitter.com/home?status=(:name:) http://lernfunk.de/Main/(:portal_url:)">
										<img src="template/uni_os_red/img/share/twitter.png" alt="twitter" /> Tweet This!</a> 
									<a href="http://www.facebook.com/sharer.php?u=http://lernfunk.de/Main/(:portal_url:)">
										<img src="template/uni_os_red/img/share/facebook.png" alt="facebook" /> Share on Facebook</a> 
									<a href="http://del.icio.us/post?url=http://lernfunk.de/Main/(:portal_url:)&title=lernfunk.de: (:name:)">
										<img src="template/uni_os_red/img/share/delicious.png" alt="delicious" /> Bookmark</a>
						</table>

						<div class="informationln" onclick="showHideSlide( '#desc_long', '#desc_sh' );">
							▴ ▴ ▴ Weniger Details ▴ ▴ ▴ </div>
					</div>
				</div>
			
			</div>

			<div class="td" id="seriesdetails_rightbox">
				<h4 class="headline">Aufzeichnungen</h4>
				<div style="max-height: 400px; overflow-y: auto; margin-top: 5px;">
					(:recordings:)
				</div>
			</div>
		</div>
	</div>
</div>
