<h3><a href="javascript: getDetails('podcast', (:id:)); ">Podcast: (:title:)</a></h3>
<div>
	<div class="tbl objcontainer">
		<div class="tr">
			<div class="td" style="max-width: 250px; min-width: 150px;">
				(:lecturer:)
				(:department:)
			</div>
			<div class="td" style="width: 100%;">
				<h4>Veranstaltung</h4>
				<div class="infoblock"><a href="javascript: getDetails('series', (:series_id:));">(:title:)</a></div>
				<h4>Typ</h4>
				<div class="infoblock">(:type:)</div>
				<h4>Abbonieren</h4>
				<div class="infoblock">
					<a href="(:url:)">
						<img src="img/rss.png" style="margin-bottom: -4px; width: 16px; height: 16px;" alt="rss" />
						RSS Feed/Podcast
					</a>
				</div>
			</div>
			<div class="td" style="padding-right: 25px;">
				<br />
				<img class="thumb" src="(:img:)" alt="(:series:)" />
			</div>
		</div>
	</div>
</div>
