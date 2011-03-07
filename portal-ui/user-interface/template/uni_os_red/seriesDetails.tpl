<script type="text/javascript">
	$('#pagetitle').text( '(:name:)' );
	$('#titlebox_bottom').hide();
</script>


<div>
	<div>(:academy:) &rarr; (:department:) &rarr; (:lecturer:)</div>
	<p id="pdct" style="margin: 5px;"></p>
	<div>
		<h4 class="headline">Beschreibung</h4>
		<p id="desc_sh" class="infoblock" style="padding: 7px 0px;">(:desc_sh:)
		<a style="cursor:pointer;" onclick="$('#desc_sh').hide(); $('#desc_long').show()"> [mehr]</a></p>
		<p id="desc_long" class="infoblock" style="display: none; padding: 7px 0px;">(:desc:)
		<a style="cursor:pointer;" onclick="$('#desc_sh').show(); $('#desc_long').hide();"> [weniger]</a></p>
	</div>

	<div class="table" style="width: 100%;">
		<div class="tr">
			<div class="td" style="width: 100%; vertical-align: top;">
				<div class="objcontainer">
					<div id="mediaobjectplayer">
						<center><img style="max-height: 300px;" src="(:(thumb):():(./template/uni_os_red/img/std_preview.jpg):():)(:thumb:)" /></center>
					</div>
				</div>
			</div>
			<div style="width: 40%; min-width: 350px; vertical-align: top; border-left: 1px solid #3E424A;">
				<h4 class="headline">Vorlesungsaufzeichnungen</h4>
				<div style="max-height: 350px; overflow-y: auto; margin-top: 5px;">
					(:recordings:)
				</div>
			</div>
		</div>
	</div>

	<p style="text-align: center;">
		Shortlink zur Veranstaltung: 
		<input style="width: 300px" type="text" id="shortlink" value="http://lernfunk.de/Main/(:portal_url:)" />
	</p>

	<script type="text/javascript">
		/* load feeds */
		var feeds = (:feeds:);
		for (var i=0; i<feeds.length; i++) {
			$('p#pdct').append('<a href="' + feeds[i].url + '">'
				+ '<img src="template/uni_os_red/img/rss.png" alt="rss" /> ' + feeds[i].type + '</a> ');
		}
		/* load first recording */
		loadRec( '#mediaobjectplayer', '(:firstrecording_cou_id:)' );
	</script>
</div>
