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
                 <a href="(:feed_url:)"><img src="template/uni_os_red/img/rss.png" style="margin-bottom: -4px; width: 16px; height: 16px;" alt="rss" /> Podcast</a>
                 <br/>
                 <br/>
                 <div>
                         <h4>Beschreibung</h4>
                         <p id="desc_sh" class="infoblock">(:desc_sh:)... <a style="cursor:pointer;" onclick="$('#desc_sh').css('display','none'); $('#desc_long').css('display','block');">mehr.</a></p>
                         <p id="desc_long" class="infoblock" style="display: none;">(:desc:) <a style="cursor:pointer;" onclick="$('#desc_sh').css('display','block'); $('#desc_long').css('display','none');">weniger</a></p>
                 </div>

         <table style="width: 100%; padding-top: 0px;">
                 <tr>
                         <td>
                                 <div class="objcontainer">
                                         <div id="mediaobjectplayer">
                                                 <center><img src="(:thumb:)" /></center>
                                         </div>
                                 </div>
                         </td>
                         <td style="width: 40%; min-width: 300px; vertival-align: top;">
                                 <h4>Vorlesungsaufzeichnungen</h4>
                                 <div style="padding-left: 20px; max-height: 350px; overflow-y: auto; border-left: 2px solid #bbbbbb;">
                                         (:recordings:)
                                 </div>
                         </td>
                 </tr>
         </table>


                <!--
                <script type="text/javascript">
                        loadPlayer( '#mediaobjectplayer', '(:firstrecording_title:)',
                                        '(:firstrecording_mimetype:)', '(:firstrecording_url:)' );
                </script>
                -->


</div>