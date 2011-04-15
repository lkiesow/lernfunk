<!-- show preview image. onclick: load video -->
<div class="new_recording">
	<a href="#resultfilter=series&cmd=search&filter=(:seriesname:)&details=1&mediatype=series&identifier=(:series_id:)">(:title:)</a>
	<div style="position: relative;">
		<p class="home_preview_par"
			onclick=" replaceBy( this.parentNode, '(:mediatype:)', '(:url:)'); ">

			<img class="home_preview" src="(:img:)" alt="(:title:)" />
			<img class="playbutton"   src="template/uni_os_red/img/playbutton.png" alt="play" />

		</p>
	</div>
</div>
