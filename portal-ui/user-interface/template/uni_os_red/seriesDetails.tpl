<script type="text/javascript">
	$('#pagetitle').text( '(:name:)' );
	$('#titlebox_bottom').hide();
</script>


<div>
	<div>(:academy:) &rarr; (:department:) &rarr; (:lecturer:)</div>
	<p id="pdct" style="margin: 20px 5px 10px 0px;"></p>
	

	<div class="table" style="width: 100%;">
		<div class="tr">
			<div class="td" style="width: 100%; vertical-align: top; padding: 0px 25px 0px 0px; ">
				<div>
          <h4 class="headline">Beschreibung</h4>
            <p id="desc_sh" class="infoblock" style="padding: 7px 0px; border-bottom: 1px solid #DCDCDC;">
              <a id="more" class="nohover" style="cursor:pointer;" onclick="$('#desc_sh').hide(); $('#desc_long').show()">
                <img src="./template/uni_os_red/img/plus.png" alt="[mehr]" style="width: 15px;">
              </a>(:desc_sh:)...
            </p>                                                                                                                                          
            <p id="desc_long" class="infoblock" style="display: none; padding: 7px 0px; border-bottom: 1px solid #DCDCDC;">
              <a id="less" class="nohover" style="cursor:pointer;" onclick="$('#desc_sh').show(); $('#desc_long').hide()">
                <img src="./template/uni_os_red/img/minus.png" alt="[weniger]" style="width: 15px;">
              </a>(:desc:) 
		            <br />
		            <br />
                <span>Direktlink: <input style="border:1px solid #aaaaaa; width: 200px" type="text" id="shortlink" value="http://lernfunk.de/Main/(:portal_url:)" />
                  <iframe src="http://www.facebook.com/plugins/like.php?href=http://lernfunk.de/Main/(:portal_url:)&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=like&amp;font=tahoma&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; float:right; overflow:hidden; width:450px; height:35px;" allowTransparency="true"></iframe>
                </span>
            </p>
        </div>
          <div class="objcontainer">
					  <div id="mediaobjectplayer">
						  <center><img style="max-height: 300px;" src="(:(thumb):():(./template/uni_os_red/img/std_preview.jpg):():)(:thumb:)" /></center>
					  </div>
</div>
			</div>
			<div style="width: 40%; min-width: 350px; vertical-align: top; border-left: 1px solid #3E424A;">
				<h4 class="headline">Aufzeichnungen</h4>
				<div style="max-height: 400px; overflow-y: auto; margin-top: 5px;">
					(:recordings:)
				</div>
			</div>
		</div>
	</div>

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
