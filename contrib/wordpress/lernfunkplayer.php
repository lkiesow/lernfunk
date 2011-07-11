<?php
/*
Plugin Name: Lernfunk Player
Plugin URI: http://www.lernfunk.de
Description: Insert Lernfunk videos in post using bracket method. Enables YouTube blogging to standalone wordpress setups.

Based on the Quicktime Posting plugin by Shawn Van Every.

Author: Andre Klassen
Version: 0.2 alpha
Author URI: http://lernfunk.de
*/ 


require_once "LernfunkWebservice.class.php";


/**
 * lernfunk_post - realises the embed mechnisms for embedding lerfunk
 *
 *
 */

function lernfunk_post($post_content)
{	$start        = "[lernfunk=";
	$end          = "]";
	$slen 	      = strlen($start);
	$patterns     = array('/\[lernfunk=/','/\]/');
	$replace      = array('','');
	
	//$content = explode(" ",$post_content);
        $nc = $post_content;

        // Let's see whether someone wants to embed Lernfunk-content
        if(strpos($post_content, "[lernfunk") !== false) {
            preg_match_all("/\[lernfunk\=(?P<digit>\d+)\]/", $nc, $matches, PREG_SET_ORDER);

            // dive into the matches
            foreach($matches as $match) {
                $recording_id = $match['digit'];

                //Let's get an Webservice for our recording
                $webservice = new LernfunkWebservice($recording_id );
                $recording = $webservice->get_recording();

                if(isset($recording['error'])){
                    // 'autschn' nothing found
                    $embed = "<br><center><b>Konnte die Aufzeichnung mit der ID: {$recording_id} nicht finden!</b><center>";
                } elseif($recording['access']!= 'Öffentlich') {
                    $embed = "<br><center><b>Die Aufzeichnung mit der ID: {$recording_id} ist nicht öffentlich!</b><center>";
		} else {
                    // check the format..
                    if(in_array($recording['format_id'],array('23','29'))) {
                        $embed = $recording['embed'];
                   } else {
                        $embed = getRed5($recording);
                    }
                }
                $nc = preg_replace("/\[lernfunk=".$recording_id ."\]/", $embed, $nc,1);
            }

        
        }
        return $nc;
}


/**
 * getRed5 - creates an red5player
 *
 *
 */


function getRed5($recording) {
	$a = array(15,14);
	$b =  array(2);
	if(in_array($recording['format_id'],$a) && !is_null($recording['preview_url'])) $stream = $recording['preview_url'];
	else if(in_array($recording['format_id'],$b) && !is_null($recording['url'])) $stream = $recording['url'];

	$related = "Wordpress-Blog";

	$embed .= '<object codeBase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab" height="380" width="380" c
lassid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
        $embed .= '<param name ="movie" value="http://vm057.rz.uos.de:8080/virtpresenter/VirtpresenterClient/blogplayer.swf"/>';
        $embed .= '<param name="quality"value="high"/>';
        $embed .= '<param name="bgcolor" value="#FFFFFF"/>';
        //$embed .= '<param name="allowScriptAccess" value="sameDomain"/>';
        $embed .= '<param name="allowFullScreen" value="true"/>';
        $embed .= '<param name="FlashVars" VALUE="red5URL=' . $stream . '&related=' . $related . '"> ';
        $embed .= '<embed width="380" height="380" allowfullscreen="true" type="application/x-shockwave-flash" pluginspage="http://ww
w.macromedia.com/go/getflashplayer" flashvars="red5URL=' . $stream .'&related=' . $related . '" quality="high" src="http://vm057.rz.uos.de:80
80/virtpresenter/VirtpresenterClient/blogplayer.swf"/>';
        $embed .= '</object>' . "\n";

        return $embed;



}


add_filter('the_content', 'lernfunk_post');
add_filter('the_excerpt','lernfunk_post');
?>
