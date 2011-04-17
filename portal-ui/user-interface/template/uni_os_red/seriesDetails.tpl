<script type="text/javascript" src="template/uni_os_red/seriesDetails.js"></script>

<div class="table" style="width: 100%;">
	<div class="tr">
		<div class="td" id="seriesdetails_leftbox">

			<div class="objcontainer" style="min-width: 340px;">
				<div id="mediaobjectplayer">
						<img style="max-height: 300px;" 
							src="(:(thumb):():(./template/uni_os_red/img/std_preview.jpg):():)(:thumb:)" />
				</div>
			</div>			
		</div>

		<div class="td" id="seriesdetails_rightbox">
			<h4 class="headline">Veranstaltungsdetails</h4>

			<div id="desc_sh" class="infoblock" style="background-color: #FEFEFE;">
				<div style="padding: 7px 0px;">(:desc_sh:)...</div>
				<div class="informationln" 
					onclick="showHideSlide( '#desc_sh', '#desc_long' );">
					&#9660;&#8195;Mehr Details&#8195;&#9660;
				</div>
			</div>

			<div id="desc_long" class="infoblock hidden" 
				style="background-color: #FEFEFE;">
				<div style="padding: 7px 0px;">(:desc:)</div>

				<table style="padding-bottom: 10px;">
					<tr>
						<td> Direktlink: </td>
						<td> <input type="text" id="shortlink" 
							value="http://lernfunk.de/Main/(:portal_url:)" /> </td>
					</tr>
					<tr id="feeds">
						<td> Feeds: </td>
						<td id="pdct">
							<a href="http://lernfunk.de/portal-ui/feed-generator/?series=(:id:)"
							><img src="template/uni_os_red/img/rss.png" alt="rss" /> Veranstaltungsfeed</a>
						</td>
					</tr>
					<tr id="share">
						<td> Share: </td>
						<td> 
							<a href="(:share_twitter:)">
								<img src="template/uni_os_red/img/share/twitter.png" 
									alt="twitter" /> Tweet This!</a> 
							<a href="(:share_facebook:)">
								<img src="template/uni_os_red/img/share/facebook.png" 
									alt="facebook" /> Share on Facebook</a> 
							<a href="(:share_delicious:)">
								<img src="template/uni_os_red/img/share/delicious.png" 
									alt="delicious" /> Bookmark</a>
						</td>
					</tr>
				</table>

				<div style="padding: 10px 0px; border-top: 1px solid #cccccc;">
					<div id="academies"   style="display: none; font-weight: bold;">&rarr;</div> 
					<div id="departments" style="display: none;">&rarr;</div> 
					<div id="lecturer"    style="display: inline;"> </div> 
				</div>

				<div class="informationln" onclick="showHideSlide( '#desc_long', '#desc_sh' );">
					&#9650;&#8195;Weniger Details&#8195;&#9650;
				</div>
			</div>

			<h4 class="headline" style="margin-top: 10px">Aufzeichnungen</h4>
			<div id="recordinglinkblock">
				(:recording_html:)
			</div>

		</div>
	</div>
</div>
