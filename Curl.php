<?php

if(!defined('INSIDE')) die('No content here!');

/**
 * Simple class for CURL execution
 *
 * @author lepi
 */
class Curl {
    private $_response;
    private $_curl;
    /**
     *
     * @var Array default CURL options
     */
    private $_options = array(
        CURLOPT_POST => 0,
        CURLOPT_HEADER => 0,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1',
        CURLOPT_HEADER =>
            array(
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                "Accept-Language: en-US,en;q=0.8,sr;q=0.6,hr;q=0.4",
                "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7",
                "Keep-Alive: 300"
            ),
        CURLOPT_SSL_VERIFYPEER => false
    );

    public function __construct($url, Array $options = NULL) {
        if($options)
            $this->_options = $options + $this->_options; //adds new option and overrides defaults
        $this->_options[CURLOPT_URL] = $url;
        $this->_curl = curl_init();
    }
    public function close() {
        
        curl_close($this->_curl);
        
    }
    /**
     * 
     * @param array $queryArray - new query parameters,
     * see processQuery method
     * @return string
     * @throws Exception
     */
    public function exec(Array $queryArray = NULL) {
        
        if ($queryArray)
            $this->processQuery($queryArray);
       
        curl_setopt_array($this->_curl, $this->_options);
        $this->_response = curl_exec($this->_curl);
        
        if (!$this->_response)
            throw new Exception(
                    'Curl execution failed. Error: ' . curl_error($this->_curl) 
                    . ', no: ' . curl_errno($this->_curl));
        
        $this->_status = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        
        return $this->_response;
    }
    /**
     * 
     * @return int HTTP respose code
     */
    public function getHttpStatus() {
        return $this->_status;
    }
    /**
     * 
     * @return string response
     */
    public function getResponse() {
        return $this->_response;
    }
    /**
     * Appends $queryArray to existing query
     * If parameter is set to === NULL, it'll be removed
     * 
     * @param Array $queryArray query in array
     */
    protected function processQuery(Array $queryArray){
        
        if ($this->_options[CURLOPT_POST])
            $this->_options[CURLOPT_POSTFIELDS] = http_build_query($queryArray);
        else {
            
            $urlArr = parse_url($this->_options[CURLOPT_URL]);
            
            if(isset($urlArr['query'])){
                $urlQuery = parse_str($urlArr['query']);
                $urlArr['query'] = $queryArray + $urlQuery['query'];
            }else $urlArr['query'] = $queryArray;
           
            if(!isset($urlArr['path'])) $urlArr['path'] = '';
            
            foreach ($urlArr['query'] as $ua)
                if($ua === NULL) unset($ua);
            $this->_options[CURLOPT_URL] = $urlArr['scheme'].'://'.$urlArr['host'].$urlArr['path'].'?'.http_build_query($urlArr['query']);
        }
    }
    /**
     * Sets options
     * @param Array $options CURL options 
     */
    public function setOptions(Array $options){
        $this->_options = $options + $this->_options;
    }
}
