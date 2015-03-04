<?
/* * *************************************************************
 * Copyright notice
 *
 * (c) 2014 Chi Hoang (info@chihoang.de)
 *  All rights reserved
 *
 * **************************************************************/
require_once('fractal.php');

$f = new fractal();
$f->method=2; //0=mandelbrot,1=julia,2=burning ship
$f->len=30; //size
$f->iterations = (integer)100;
$im = $f->render(); 

$filename = '/tmp/fr_'.substr(md5(time()), 0,7).'.png';

if (!$handle = fopen($filename, "wb")) {
     print "Can't open file $filename";
     exit;
}
if (!fwrite($handle, $im)) {
   print "Can't write $filename";
   exit;
} 
fclose($handle);

echo $filename.'|'.$f->startRe.'|'.$f->startIm.'|'.$f->endeRe.'|'.$f->endeIm.'|'.$f->stepsRe.'|'.$f->stepsIm;

?>
