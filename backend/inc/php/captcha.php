<?php
//super-simple Captcha to protect against brute-force-Attacks
session_start();
$ch = '23456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';// 0/1 look like O/l
$s = '';
for($i=0; $i < 5; $i++) $s .= $ch[mt_rand(0, strlen($ch)-1)];
$_SESSION['captcha_answer'] = $s;
$f = 5;
$w = imagefontwidth($f) * strlen($s);
$h = imagefontheight($f)+3;
$i = imagecreatetruecolor ($w, $h);
$c1 = imagecolorallocate ($i, mt_rand(150,255), mt_rand(150,255), mt_rand(150,255));
$c2 = imagecolorallocate ($i, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
imagefill($i,0,0,$c1);
imagestring($i,$f,0,0,$s,$c2);
$i = imagerotate($i, mt_rand(-5,5), $c1);
header('Content-type: image/png');
imagepng($i);
imagedestroy($i);
?>
