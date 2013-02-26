<?php

// 模拟延时发送

$a = range('a', 'z');
foreach (range(1, 10) as $i) {
	echo $a[mt_rand(0, 3)].','.str_repeat('a', mt_rand(10, 20));
	echo "\n";
	sleep(1);
}
