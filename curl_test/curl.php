<?php
echo memory_get_usage(), "\n";

$sURL = 'http://royal-us.socialgamenet.com/time.php';

$fTime = microtime(TRUE);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $sURL);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

$sReturn = curl_exec($curl);
curl_close($curl);
echo microtime(TRUE) - $fTime, "\n";

var_dump(isset($curl));
exit;
/*
 */

$sURLa = 'http://royal-us.socialgamenet.com/';

$curla = curl_init();
curl_setopt($curla, CURLOPT_URL, $sURLa);
curl_setopt($curla, CURLOPT_HEADER, FALSE);
curl_setopt($curla, CURLOPT_RETURNTRANSFER, TRUE);

$fTime = microtime(TRUE);

echo memory_get_usage(), "\n";

$mh = curl_multi_init();
curl_multi_add_handle($mh, $curl);
curl_multi_add_handle($mh, $curla);

curl_multi_exec($mh);
do {
	curl_multi_exec($mh, $active);
} while ($active);

echo memory_get_usage(), "\n";

echo microtime(TRUE) - $fTime, "\n";

echo curl_multi_getcontent($curl);
echo curl_multi_getcontent($curla);

curl_close($curl);
curl_close($curla);

echo memory_get_usage(), "\n";
