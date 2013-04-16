<?php

	header( 'Content-Type: text/plain; charset=utf-8' );
	require_once( dirname(__FILE__).'/lf_sql.php' );

	require_once( dirname(__FILE__).'/lf_player_plugins.php' );

	if ( array_key_exists( 'id', $_REQUEST ) ) {

		$query = 'select url, preview_url, name from mediaobject m '
			.'left outer join format f on f.format_id = m.format_id '
			.'where object_id = '.mysql_escape_string($_REQUEST['id']).';';

		if ( $rs = lf_query( $query ) ) {
			if ( $r = mysql_fetch_array( $rs, MYSQL_ASSOC ) ) {

				if ( array_key_exists( $r['name'], $plugins ) ) {
					echo call_user_func( $plugins[ $r['name'] ], $r['url'], $r['preview_url'], $_REQUEST);
				
				} else {
					echo "No handler for media format ".$r['name'].".\n";
				}
			} else {
				echo "No accessible mediaobject with id ".$_REQUEST['id'].".\n";
			}
		}
	} else {
		echo "You must specify an id parameter.\n";
	}

?>
