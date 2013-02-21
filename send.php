<?php
// http://wiki.socialgamenet.com/display/core/Application+Log+System

class ALS {

	static $_conn;
	static $_logPool = array();

	static public function send() {
		if (!self::$_logPool) {
			return FALSE;
		}
		if (!self::$_conn) {
			if (self::$_conn === FALSE) {
				return FALSE;
			}
			if (!self::$_conn = fsockopen('unix:///tmp/syslog.sock')) {
				return FALSE;
			}
		}
		foreach (self::$_logPool as $sSend) {
			echo $sSend;
			fwrite(self::$_conn, ':'.$sSend."\n");
		}
	}

	static public function log($sNamespace, $mMessage) {
		list($fMtime, $iTime) = explode(' ', microtime());
		$iMtime = $iTime * 1000 + round($fMtime * 1000, 0);
		self::$_logPool[] = $sNamespace.','.$iMtime.','.json_encode($mMessage);
	}
}

//ALS::log('abc', array(str_repeat('a', mt_rand(40000, 50000))));
//ALS::log('abc', array(str_repeat('a', 101000)));
//ALS::log('abc', array(str_repeat('a', 13)));
ALS::log('abc', date('H:i:s'));
ALS::send();
