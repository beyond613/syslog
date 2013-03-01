#! /usr/bin/env php
<?php

// 用于主机自检

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
		fwrite(
			self::$_conn,
			':'.$sNamespace.','.$iMtime.','.json_encode($mMessage)."\n"
		);
	}
}

$lReturn = array();
exec('/sbin/ifconfig', $lReturn);

echo $sSend = implode("\n", $lReturn);

ALS::log('server_test', $sSend);
