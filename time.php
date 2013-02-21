<?php

// 测试单次 unix socket 发送时间

$a = array(date('Y-m-d H:i:s'), str_repeat('a', 5000));
$sSend = json_encode($a);

$f = microtime(TRUE);
$conn = fsockopen('unix:///tmp/als.sock');
fwrite($conn, ':'.$sSend."\n");
echo sprintf('%0.06f', microtime(TRUE) - $f), "\n";

$f = microtime(TRUE);
fwrite($conn, ':'.$sSend."\n");
echo sprintf('%0.06f', microtime(TRUE) - $f), "\n";
