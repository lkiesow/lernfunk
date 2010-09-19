<!-- show preview image. onclick: load video -->
<div class="new_recording">(:mediatype:): (:title:)
<div style="font-size: smaller;">(:date:)</div>
	<p style="cursor: pointer; text-align: center;" >
			<img src="(:img:)" alt="(:title:)" style="max-width: 280px;" 
				onclick=" replaceBy( this.parentNode, '(:mediatype:)', '(:url:)'); "/>
	</p>
</div>
