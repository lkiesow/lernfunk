<?php
   if (!array_key_exists( 'user' , $_REQUEST ))
      exit();

	/* Send login request. */
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, 'http://studip.uni-osnabrueck.de/extern.php'
		.'?module=TemplatePersondetails&range_id=studip'
		.'&config_id=0d0841fe399bccdf15bcafd8c9a54680'
		.'&username='.$_REQUEST['user'] );
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt( $ch, CURLOPT_USERAGENT,
		'Opera/9.80 (X11; Linux x86_64; U; en) Presto/2.8.131 Version/11.11' );
	$content = curl_exec( $ch );
	$response = curl_getinfo( $ch );
	curl_close ( $ch );

	header( 'Content-type: text/plain' );
	echo trim( $content );
?>
