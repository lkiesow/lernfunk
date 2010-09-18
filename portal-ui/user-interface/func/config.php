<?

/* configuration */

error_reporting(E_ALL);
ini_set("display_errors", true);

$cfg = array(
	// how much news are shown
	'newscount'      => 5,
	'newreccount'    => 2,

	// filter recordings that are shown on the startpage by mimetype
	'newsrecfilter'  => array( '%video%' ),

	// how much objects should be displayed
	'objectsPerPage' => 4,

	// adress of the preview image shown when nothing is defined in database
	'stdPreviewImg'  => 'img/std_preview.jpg',

	// template to use
	'tplName'        => 'white_base_scv-70',

	// webservice definition
	'webservices'    => array(
		array(
			'name' => 'Lernfunk',
			'url'  => 'http://vm083.rz.uos.de/webservices/portal/',
			'key'  => 'SWUZkIwX6BIV!'
		)
	)
);

$includes = '
	<!-- jQuery UI -->
	<link type="text/css" href="template/'.$cfg['tplName'].'/css/jquery/jquery-ui-1.7.2.custom.css" rel="Stylesheet" />
	<script type="text/javascript" src="./js/imports/jquery/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="./js/imports/jquery/jquery-ui-1.7.2.custom.min.js"></script>

	<script type="text/javascript" src="./js/imports/jquery-json/jquery.json-2.2.min.js"></script>
	<script type="text/javascript" src="./js/imports/jquery-hashchange/jquery.ba-hashchange.min.js"></script>
	<script type="text/javascript" src="./js/imports/jquery-bbq/jquery.ba-bbq.min.js"></script>

	<!-- fancybox -->
	<script type="text/javascript" src="./js/imports/jquery-fancybox/jquery.mousewheel-3.0.2.pack.js"></script>
	<script type="text/javascript" src="./js/imports/jquery-fancybox/jquery.fancybox-1.3.1.js"></script>
	<link rel="stylesheet" type="text/css" href="./js/imports/jquery-fancybox/jquery.fancybox-1.3.1.css" media="screen" />

	<link rel="stylesheet" type="text/css" href="template/'.$cfg['tplName'].'/css/style.css" />
	<script type="text/javascript" src="./php/template-js.php?x=y.js.js"></script>
	<script type="text/javascript" src="./php/config-js.php?x=y.js.js"></script>
	<script type="text/javascript" src="./js/func.js"></script>
';

?>