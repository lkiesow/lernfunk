<h3>Vorlesung: (:name:)
<div style="float: right; font-size: smaller; font-weight: normal; padding-right: 20px;">(:term:)</div>
</h3>
<!--
<div style="margin-top: -10px; padding-top: 0px; padding-left: 50px;">
	<div style="float: right; padding-right: 15px;">
		<a style="font-size: smaller;" href="(:feed_url:)"><img src="template/white_base_scv-70/img/rss.png" 
			style="margin-bottom: -4px; width: 16px; height: 16px;" alt="rss" /> RSS-Feed/Podcast</a>
	</div>
		(:academy:) &rarr; (:department:) &rarr; (:lecturer:)
</div>
-->
<div>
		<div style="width: 100%; margin-top: -20px; padding-top: 0px;" class="table">
			<div style="width: 100%" class="tr">
				<div style="width: 100%" class="td">
					(:academy:) &rarr; (:department:) &rarr; (:lecturer:)
				</div>
				<div class="td" style="text-align: right;">
					(:(feed_url):():():(<a style="font-size: smaller;" href="):) (:feed_url:) (:(feed_url):():():("><img src="template/white_base_scv-70/img/rss.png" style="margin-bottom: -4px; width: 16px; height: 16px;" alt="rss" /></a>):)
				</div>
			</div>
		</div>
	<div class="objcontainer">
		<div class="table">
			<div class="tr">
				<div class="td">
					<div id="mediaobjectplayer">
						<h4>Beschreibung</h4>
						<div class="infoblock">(:desc:)</div>
						<center><img src="(:thumb:)" /></center>
					</div>
				</div>
				<div class="td" style="min-width: 300px;">
					<h4>Vorlesungsaufzeichnungen</h4>
					<div style="padding-left: 20px; max-height: 350px; overflow-y: auto; border-left: 2px solid #bbbbbb;">
						(:recordings:)
					</div>
				</div>
			</div>
		</div>

		<!--
		<script type="text/javascript">
			loadPlayer( '#mediaobjectplayer', '(:firstrecording_title:)', 
					'(:firstrecording_mimetype:)', '(:firstrecording_url:)' );
		</script>
		-->

	</div>
</div>
