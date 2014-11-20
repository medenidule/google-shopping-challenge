<?php

if(!defined('INSIDE')) die('No content here!');

require_once 'Curl.php';
require_once 'simple_html_dom.php';
/**
 * Scrapes the prices for a given product EAN code from Google Shopping
 *
 * @author lepi
 */
class GoogleShopping {
    
    protected $searchURL = 'https://www.google.nl/search';
    protected $seachQuery = 
            array(
                'hl' => 'nl',
                'output' => 'search',
                'tbm' => 'shop'
            );
    /**
     * 
     * @param string $ean - EAN code of a product
     * @return Array - Array of Arrays of Sellers an Prices
     */
    public function getPrices($ean){
        
        $this->seachQuery['q'] = sprintf('%014s', $ean); //padding left to 14 chars by 0s
        
        $curlSearch = new Curl($this->searchURL);
        
        try{
            
            $htmlSearch = $curlSearch->exec($this->seachQuery);
            
        }  catch (Exception $e){
            outputError('Search CURL error: ' . $e->getMessage());
        }        
        $curlSearch->close();
        
        if($htmlSearch){
            $htmlSearch = strstr($htmlSearch, '<html'); //clean content before html opening tag

            $href = $this->findFirstProductURL($htmlSearch);

            $url = $this->getPricesURL($href);

            $curlPrices = new Curl($url);

            try{
                $htmlPrices = $curlPrices->exec();
            }  catch (Exception $e){
                outputError('Prices CURL error: '.$e->getMessage());
            }
            $curlPrices->close();
            
            if($htmlPrices)
                $htmlPrices = strstr($htmlPrices, '<html'); //clean content before html opening tag
            else return false;
            
            return $this->findPrices($htmlPrices);
        }else return false;
    }
    /**
     * Converts href of product to URL of prices
     * @param string $href
     * @return string
     */
    private function getPricesURL($href){
        $href = mb_substr($href, 0, strpos($href, '?')); //remove query
        return 'https://www.google.nl' . $href . '/online?hl=nl'; //build URL for prices
    }


    /**
     * Extract sellers and prices from HTML
     * @param string $html 
     * @return Array - Array of Arrays of Sellers an Prices
     */
    protected function findPrices($html){
        $htmlParser = str_get_html($html); // get parser
        
        $sellers = $htmlParser->find('span.os-seller-name-primary a'); //seller names tags
        $prices = $htmlParser->find('span.os-base_price'); //prices tags
        
        if($sellers && $prices){
            $i = 0;
            foreach($sellers as $seller){ // assumed that there are the same number of sellers and prices
                $results[$i]['seller'] = $seller->plaintext;
                $results[$i]['price'] = filter_var($prices[$i]->plaintext, FILTER_SANITIZE_NUMBER_FLOAT)/100; //extract float and convert to number
                $i++;
            }

           return $results;
        }else return false;
    }
    /**
     * returns url of the first product in $html
     * @param string $html
     * @return string
     */
    protected function findFirstProductURL($html){
       
        $htmlParser = str_get_html($html);
        
        $a = $htmlParser->find('h3.r a', 0); //
        if($a)
            return $a->href;
        else return false;
    }

}
