<?

header('Content-Type: text/plain; charset=utf-8');
require_once(dirname(__FILE__).'/config.php');



function mkjstpl( $dir, $jsclass ) {

	echo $jsclass." = {}\n";
	$cats = scandir( $dir );

	foreach( $cats as $id => $cat ) {
		if ( $cat[0] != '.' ) {
			if (is_dir( $dir.'/'.$cat )) {
				mkjstpl( $dir.'/'.$cat, $jsclass.'.'.$cat );
			} else {
				if (substr( $cat, -4, 4) == '.tpl') {
					echo $jsclass.'.'.substr( $cat, 0, strlen($cat)-4 )." = \n";
					/* TODO: print file-contents ... */
					$search  = array( "\r", "'", "\n" );
					$replace = array( '', "\\'", "\\n'\n+ '" );
					echo "'".str_replace( $search, $replace, file_get_contents( $dir.'/'.$cat ) )."';\n";
					//echo "'".str_replace( "\n", "\\n'\n+ '", str_replace( "\r", '', file_get_contents( $dir.'/'.$cat ) ) )."';\n";
				}
			}
		}
	}

}

mkjstpl( dirname(__FILE__).'/../template/'.$cfg['tplName'].'/instant', 'tpl' );
?>
