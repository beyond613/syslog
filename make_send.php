<?php

// 向 socket 大量发送

class ALS {

	static $_conn;

	static public function log($sNamespace, $mMessage) {

		if (!self::$_conn) {
			if (self::$_conn === FALSE) {
				return FALSE;
			}
			if (!self::$_conn = fsockopen('unix:///tmp/als.sock')) {
				return FALSE;
			}
		}

		list($fMtime, $iTime) = explode(' ', microtime());
		$iMtime = $iTime * 1000 + round($fMtime * 1000, 0);

		$sContent = $iMtime.',';
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			$sContent .= json_encode($mMessage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
		} else {
			$sContent .= json_encode($mMessage)."\n";
		}
		$sSend = ':'.$sNamespace.','.$sContent;

		fwrite(self::$_conn, $sSend);
	}
}

$c = 0;

foreach (range(1, 70000) as $i) {

	$sCategory = 'h'.mt_rand(0, 4);
	$sCategory = 'h1';

	$c++;
	$aMsg = array(
		'id' => ' '.$i.' ',
		'content' => '中文 '.microtime().' '.$c.' '.mt_rand(),
		'datetime' => date('Y-m-d H:i:s'),
		'dummy' => str_repeat('a', 1200),
	);
	$aMsg['md5'] = md5($aMsg['content']);

	ALS::log($sCategory, $aMsg);
}
