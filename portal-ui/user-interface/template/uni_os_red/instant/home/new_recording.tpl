<!-- show preview image. onclick: load video -->
<div class="new_recording"><a href="#resultfilter=series&cmd=search&filter=(:seriesname:)&details=1&mediatype=series&identifier=(:series_id:)">(:title:)</a>
        <p style="cursor: pointer; text-align: left; padding-left: 20px;" >
                        <img src="(:img:)" alt="(:title:)" style="padding-top: 10px; max-width: 280px;"
                                onclick=" replaceBy( this.parentNode, '(:mediatype:)', '(:url:)'); "/>
        </p>
</div>