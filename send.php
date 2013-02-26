<?php
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
		var_dump($mMessage);
		fwrite(
			self::$_conn,
			':'.$sNamespace.','.$iMtime.','.json_encode($mMessage)."\n"
		);
	}
}

// foreach (array(0, mt_rand(10, 20)) as $i) {
foreach (array(0, 1) as $i) {
	ALS::log('z'.mt_rand(0,3), array(date('H:i:s'), $i));
}
