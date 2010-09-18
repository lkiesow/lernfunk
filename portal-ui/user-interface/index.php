<?

/* includes */

require_once(dirname(__FILE__).'/func/config.php');

$tok = array( '(:includes:)', '(:tplpath:)' );
$rep = array( $includes, 'template/'.$cfg['tplName'] );
echo str_replace( 
	$tok, $rep,
	file_get_contents( dirname(__FILE__).'/template/'.$cfg['tplName'].'/index.tpl' ) );


?>
