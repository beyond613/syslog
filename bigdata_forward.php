#! /usr/bin/env php
<?php

ini_set('memory_limit', 200000000);

class Forward {

	protected static $_iPool   = 0;
	protected static $_lPoolH  = array();
	protected static $_lPoolMH = array();

	protected static $_aConfig;

	public static function check() {

		foreach (self::$_lPoolMH as $i => $mh) {

			$active = 1;
			curl_multi_exec($mh, $active);

			if (!$active) {

				curl_close(self::$_lPoolH[$i]);
				unset(self::$_lPoolH[$i]);

				curl_multi_close(self::$_lPoolMH[$i]);
				unset(self::$_lPoolMH[$i]);
			}
		}

		return count(self::$_lPoolMH);
	}

	public static function send($sCategory, $sMessage) {

		/*
		// for debug in local
		file_put_contents('/tmp/farmlog.d/als', '['.$sCategory.','.$sMessage."]\n", FILE_APPEND);
		return TRUE;
		 */

		$aConfig =& self::$_aConfig;
		if (!$aConfig) {
			$aConfig = require __DIR__.'/bigdata_config.inc.php';
		}

		self::$_iPool++;

		$curl = curl_init();
		$sURL = $aConfig['url'].$aConfig['app_alias'].'/';
		curl_setopt($curl, CURLOPT_URL, $sURL);

		echo $sURL;

		$iTime = time();
		$aSend = array(
			'd' => $sMessage,
			't' => $iTime,
			's' => md5($aConfig['secret_key'].'i'.$aConfig['app_alias'].$iTime),
		);

		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($aSend));

		$s = curl_exec($curl);
		echo var_dump($s);
		curl_close($curl);

		return TRUE;
	}
}

$h = fopen('php://stdin', 'rb');
stream_set_read_buffer($h, 0);
stream_set_blocking($h, 0);

$sBuffer = '';
$lSend = array();

$iLoopTimeOld = 0;

do {

	$bEOF = feof($h);

	$sRead = fread($h, 65536);

	if (strlen($sRead) > 0) {

		/*
		debug
		file_put_contents(
			'/tmp/farmlog.d/size',
			'sBuffer = '.strlen($sBuffer).', sRead = '.strlen($sRead)."\n",
			FILE_APPEND | LOCK_EX
		);
		 */

		$sBuffer .= $sRead;

		while (($iPos = strpos($sBuffer, "\n")) !== FALSE) {
			$sLine = substr($sBuffer, 0, $iPos);
			$sBuffer = substr($sBuffer, $iPos + 1);

			if (substr($sLine, 0, 1) === ':') {
				$sLine = substr($sLine, 1);
			}

			$sCategory = 'bigdata';
			$sMessage = $sLine;

			$aSend =& $lSend[$sCategory];
			if (!$aSend) {
				$aSend = array(
					'time' => time(),
					'msg' => $sMessage,
				);
			} else {
				$aSend['msg'] .= "\n".$sMessage;
			}
			unset($aSend);
		}

		$bSleep = FALSE;

	} else {

		$bSleep = TRUE;
	}

	$iCheck = 0;

	foreach ($lSend as $sCategory => $aSend) {

		$bSend =
			$bEOF
			|| (time() - $aSend['time']) > 5
			|| strlen($aSend['msg']) > 10000000;

		if (!$bSend) {
			continue;
		}

		Forward::send($sCategory, $aSend['msg']);

		unset($lSend[$sCategory]);
	}

	if ($bSleep) {
		usleep(2000);
		$iCheck = Forward::check();
	}

	/*

	// for debug cURL handle
	$iLoopTime = time();
	if ($iLoopTimeOld != $iLoopTime) {
		file_put_contents(
			'/tmp/farmlog.d/forward.log',
			date('Y-m-d H:i:s', $iLoopTime).' '.sprintf('%3d', $iCheck).' '.memory_get_usage()."\n",
			FILE_APPEND | LOCK_EX
		);
		$iLoopTimeOld = $iLoopTime;
	}
	*/

} while (!$bEOF);

while (Forward::check()) {
	sleep(1);
}
