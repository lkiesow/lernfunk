<script type="text/javascript">
        $('#pagetitle').text( '(:name:)' );
        $('#titlebox_bottom').hide();
</script>


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
        <div>(:academy:) &rarr; (:department:) &rarr; (:lecturer:)</div>
        <br/>
        (:(feed_url):():():(<a href="):)(:feed_url:)(:(feed_url):():():("><img src="template/uni_os_red/img/rss.png" style="margin-bottom: -4px; width: 16px; height: 16px;" alt="rss" /> Podcast</a>):)
        <br/>
        <br/>
        <div>
                <h4 class="headline">Beschreibung</h4>
                <p id="desc_sh" class="infoblock" style="padding: 7px 0px;">(:desc_sh:)...
                <a style="cursor:pointer;" onclick="$('#desc_sh').hide(); $('#desc_long').show()"> [mehr]</a></p>
                <p id="desc_long" class="infoblock" style="display: none; padding: 7px 0px;">(:desc:)
                <a style="cursor:pointer;" onclick="$('#desc_sh').show(); $('#desc_long').hide();"> [weniger]</a></p>
        </div>

        <div class="table" style="width: 100%;">
                <div class="tr">
                        <div class="td" style="width: 100%; vertical-align: top;">
                                <div class="objcontainer">
                                        <div id="mediaobjectplayer">
                                                <center><img src="(:(thumb):():(./template/uni_os_red/img/std_preview.jpg):():)(:thumb:)" /></center>
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

<!--
<script type="text/javascript">
        loadPlayer( '#mediaobjectplayer', '(:firstrecording_title:)',
                '(:firstrecording_mimetype:)', '(:firstrecording_url:)' );
</script>
-->

</div>