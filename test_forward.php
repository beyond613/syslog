#! /usr/bin/env php
<?php
$h = fopen('php://stdin', 'r');
while (!feof($h)) {
	$s = fgets($h);
	$l = strlen($s);
	file_put_contents(__DIR__.'/out/'.getmypid(), $l."\n", FILE_APPEND | LOCK_EX);
}
