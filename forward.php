#! /usr/bin/env php
<?php
$aConfig = require __DIR__.'/config.inc.php';

function send($sCategory, $sMessage, $aConfig) {

	$curl = curl_init();
	$sURL = $aConfig['url'].$aConfig['app_alias'].'/'.$sCategory;
	curl_setopt($curl, CURLOPT_URL, $sURL);

	$iTime = time();
	$aSend = array(
		'd' => $sMessage,
		't' => $iTime,
		's' => md5($aConfig['secret_key'].'i'.$aConfig['app_alias'].'v'.$sCategory.$iTime),
	);

	curl_setopt($curl, CURLOPT_HEADER, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($aSend));
	$sReturn = curl_exec($curl);
	curl_close($curl);

	return $sReturn === "''";
}

$h = fopen('php://stdin', 'rb');
stream_set_blocking($h, 0);

$sBuffer = '';
$lSend = array();

do {

	$bEOF = feof($h);

	$sRead = fread($h, 8192);
	if (strlen($sRead)) {
		$sBuffer .= $sRead;

		while (($iPos = strpos($sBuffer, "\n")) !== FALSE) {
			$sLine = substr($sBuffer, 0, $iPos);
			$sBuffer = substr($sBuffer, $iPos + 1);

			list($sCategory, $sMessage) = explode(',', $sLine, 2);

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
	}

	foreach ($lSend as $sCategory => $aSend) {

		$bSend =
			$bEOF
			|| (time() - $aSend['time']) > 5
			|| strlen($aSend['msg']) > 3000000;

		if (!$bSend) {
			continue;
		}

		$bSuccess = FALSE;
		foreach (range(0, 1) as $iTry) {
			if (send($sCategory, $aSend['msg'], $aConfig)) {
				$bSuccess = TRUE;
				break;
			}
		}
		if (!$bSuccess) {
			file_put_contents(__DIR__.'/test_fail_log', $sCategory.' '.strlen($aSend['msg']), FILE_APPEND);
		}
		unset($lSend[$sCategory]);
	}

} while (!$bEOF);
