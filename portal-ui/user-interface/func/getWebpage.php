<?php
   if (!array_key_exists('url' , $_REQUEST))
      exit();

   header('Content-Type: text/plain; charset=utf8');
   $src = get_src(stripslashes($_REQUEST['url']));
   $pos = strpos($src, "\r\n\r\n") + 4 ;
   if ($pos !== false) {
      echo substr($src, $pos);
   }

function get_src($url) {
	preg_match('@^(?:http://)(?<host>[^/]+)(?<path>/.*)@i', $url, $url);

	$fp = fsockopen ($url['host'], 80, $errno, $errstr, 30);
	if (!$fp) {
        $buffer = array($errstr.' ('.$errno.")\n");
    } else {
        $i = 0;
        $buffer = array();
        fputs ($fp, 'GET '.$url['path']." HTTP/1.0\r\nHost: ".$url['host']."\r\n\r\n");
        while (!feof($fp)) {
            $buffer[$i] = fgets($fp,1024);
            $i++;
        }
        fclose($fp);
    }
    return implode($buffer, '');
}
?>
