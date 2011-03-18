<!-- show preview image. onclick: load video -->
<div class="new_recording">
	<a href="#resultfilter=series&cmd=search&filter=(:seriesname:)&details=1&mediatype=series&identifier=(:series_id:)">(:title:)</a>
	<div>
		<p class="home_preview_par" onclick=" replaceBy( this.parentNode, '(:mediatype:)', '(:url:)'); ">
			<img class="home_preview" src="(:img:)" alt="(:title:)" />
			<span style="font-size: 70px;">▶</span>
		</p>
	</div>
</div>
