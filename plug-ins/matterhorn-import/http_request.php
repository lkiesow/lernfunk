<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Lars Kiesow

	This file is part of Lernfunk. 

	Lernfunk is free software: you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation, either version 3 of the License, or 
	(at your option) any later version. 

	Lernfunk is distributed in the hope that it will be useful, 
	but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
	GNU General Public License for more details. 

	You should have received a copy of the GNU General Public License 
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>. 
 */



/*******************************************************************************
 * Get url content and response headers (given a url, follows all redirections
 * on it and returned content and response headers of final url)
 * @return    array[0]    content
 * array[1]    array of response headers
 ******************************************************************************/
function get_url( $url,  $javascript_loop = 0, $timeout = 5 ) {

	$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

	$cookie = tempnam ("/tmp", "CURLCOOKIE");
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_USERAGENT,      'Opera/9.80 (X11; '
		.'Linux x86_64; U; en) Presto/2.7.62 Version/11.01' );
	curl_setopt( $ch, CURLOPT_URL,            $url );
	curl_setopt( $ch, CURLOPT_COOKIEJAR,      $cookie );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_ENCODING,       '' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_AUTOREFERER,    true );
	/* required for https urls: */
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
	curl_setopt( $ch, CURLOPT_TIMEOUT,        $timeout );
	curl_setopt( $ch, CURLOPT_MAXREDIRS,      10 );

	$content = curl_exec( $ch );
	$response = curl_getinfo( $ch );
	curl_close ( $ch );

	if ($response['http_code'] == 301 || $response['http_code'] == 302) {
		ini_set( 'user_agent', 'Opera/9.80 (X11; '
			.'Linux x86_64; U; en) Presto/2.7.62 Version/11.01');

		if ( $headers = get_headers($response['url']) ) {
			foreach( $headers as $value ) {
				if ( substr( strtolower($value), 0, 9 ) == 'location:' ) {
					return get_url( trim( substr( $value, 9, strlen($value) ) ) );
				}
			}
		}
	}

	if ( ( preg_match( "/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", 
		$content, $value )
		|| preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value) ) 
		&& $javascript_loop < 5) {
		return get_url( $value[1], $javascript_loop+1 );
	}

	return array( 'content' => $content, 'header' => $response );

}
?>
