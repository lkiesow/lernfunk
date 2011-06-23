<?php

function write_rss_item( $title, $format_id, $urls, $author, $description, $guid) {

	$desc = $title."\n"
		.$author."\n"
		.$description."\n\n"
		."Imported mediaobjects;";

	foreach ( $urls as $u => $f ) {
		$desc .= "\n"
			." → format: ".$f."\n"
			."   url   : ".$u;
	}
	$url = ''; /* generate imported mediapackage url */

	$data = "<item>\n"
		."\t<title>".$title."</title>\n"
		."\t<description>".$desc."</description>\n"
		."\t<link>".$url."</link>\n"
		."\t<guid>".$guid."</guid>\n"
		."\t<pubDate>".date( DATE_RSS )."</pubDate>\n"
		."</item>";

	file_put_contents( dirname(__FILE__).'/rss_data/'.time().$guid.'.item', $data );

}

?>
