<?php
if (!array_key_exists('request' , $_REQUEST) || !array_key_exists('url' , $_REQUEST))
	exit();

$src = get_url(stripslashes($_REQUEST['url'])
	.'?request='.urlencode(stripslashes($_REQUEST['request'])));

$gzip = 0;
foreach( $src['header'] as $h ) {
	if ( substr( strtolower($h), 0, 13 ) == 'content-type:' ) {
		header( $h );
	}
	if ( substr( strtolower($h), 0, 22 ) == 'content-encoding: gzip' ) {
		$gzip = 1;
		header( $h );
	}
}
if ( $gzip ) {
	echo gzencode($src['content']);
} else {
	echo $src['content'];
}

/**
 * Get url content and response headers (given a url, follows all redirections 
 * on it and returned content and response headers of final url)
 **/
function get_url( $url,  $javascript_loop = 0, $timeout = 5 )
{
	$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

	$cookie = tempnam ("/tmp", "CURLCOOKIE");
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_USERAGENT,      'Opera/9.80 (X11; Linux x86_64; U; en) Presto/2.7.62 Version/11.01' );
	curl_setopt( $ch, CURLOPT_URL,            $url );
	curl_setopt( $ch, CURLOPT_COOKIEJAR,      $cookie );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_ENCODING,       '' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_AUTOREFERER,    true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false ); // required for https urls
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
	curl_setopt( $ch, CURLOPT_TIMEOUT,        $timeout );
	curl_setopt( $ch, CURLOPT_MAXREDIRS,      10 );

	$content = curl_exec( $ch );
	$response = curl_getinfo( $ch );
	curl_close ( $ch );

	return array( 
		'content' => $content, 
		'info'    => $response,
		'header'  => get_headers($response['url'])
	);
}

?>
