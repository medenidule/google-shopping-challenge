<?php

define('INSIDE', 1);
define('DEBUG', 1);

require 'GoogleShopping.php';

try{

    $ean = '8806085553941';
    $crawler = new GoogleShopping;
    $prices = $crawler->getPrices($ean);
    echo "<PRE>", json_encode($prices/*, JSON_PRETTY_PRINT*/), "</PRE>";

    
}catch (Exception $e){  
    
    outputError($e->getMessage());   
    
}


function outputError(Exception $e){
    if(DEBUG) echo $e->getMessage ();
    else error_log ($e->getMessage ());
}
