<?php

$usmap = '/home/rajamuda/htdocs/mysnp/resources/phylo-tree/1/phylo_tree.svg';
$im = new Imagick();
$svg = file_get_contents($usmap);

$im->readImageBlob($svg);

$im->setImageFormat("png24");
// $im->resizeImage(720, 445, imagick::FILTER_LANCZOS, 1);  /*Optional, if you need to resize*/


header('Content-type: image/png');
echo $im;

$im->clear();
$im->destroy();

?>