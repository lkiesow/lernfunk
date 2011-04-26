<!-- show preview image. onclick: load video -->
<div class="new_recording">
	<a href="#resultfilter=series&cmd=search&filter=(:seriesname:)&details=1&mediatype=series&identifier=(:series_id:)">(:title:)</a>
	<div style="position: relative;">
		<p class="home_preview_par"
			onclick=" replaceBy( this.parentNode, '(:formatname:)', '(:url:)', '(:preview_url:)' ); ">

			<img class="home_preview" src="(:img:)" alt="(:title:)" />
			<span class="playbutton"></span>

		</p>
	</div>
</div>
