<?php

header('Content-Type: text/plain; charset=utf-8');
require_once(dirname(__FILE__).'/config.php');

echo 'cfg = ' . json_encode( $cfg );

?>
