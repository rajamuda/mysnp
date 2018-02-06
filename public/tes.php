<?php

$bt2reads = array();
foreach($_GET['bt2reads'] as $reads) {
    echo $reads.PHP_EOL;
    if(trim($reads) != '')
        $bt2reads[] = $reads;
}
//print_r($_GET['bt2reads']);
//print_r($bt2reads);
