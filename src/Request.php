<?php

namespace Pixelarbeit\Http;

use Pixelarbeit\Http\Exceptions\ConnectionException;



class Request
{
    private $url = null;
    private $method = 'GET';
    private $data = null;
    private $headers = [];

    
    private $ch;



    public function __construct()
    {
        $this->ch = curl_init();        
        
        $this->setCurlOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $this->setCurlOpt(CURLOPT_SSL_VERIFYHOST, 0);        
        $this->setCurlOpt(CURLOPT_HEADER, 1);
        $this->setCurlOpt(CURLINFO_HEADER_OUT , true);
    }



    public function setData($data)
    {
        $this->data = $data;        
    }



    public function setUrl($url)
    {
        $this->url = $url;        
    }



    public function setMethod($method)
    {
        $this->method = $method;        
    }



    public function setHeaders($headers)
    {
        $this->headers = $headers;        
    }
    
    
    
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }



    public function setCurlOpt($key, $value)
    {
        return curl_setopt($this->ch, $key, $value);
    }



    public function getError()
    {
        return curl_error($this->ch);
    }



    private function parseHeader($header)
    {
        $headers = [];
        $header = explode("\n", $header);

        for ($i = 1; $i < count($header); $i++) {
            list($key, $item) = explode(': ', $header[$i]);
            $headers[$key] = $item;
        }

        return $headers;
    }



    private function prepare()
    {
        $this->setCurlOpt(CURLOPT_CUSTOMREQUEST, $this->method);
        $this->setCurlOpt(CURLOPT_URL, $this->url);        
        $this->setCurlOpt(CURLOPT_HTTPHEADER, $this->headers);
        $this->setCurlOpt(CURLOPT_VERBOSE, true);

        if (empty($this->data) == false) {
            $this->setCurlOpt(CURLOPT_POSTFIELDS, $this->data);
            $this->setCurlOpt(CURLOPT_POST, 1);
            $this->setCurlOpt(CURLOPT_RETURNTRANSFER, 1);
        }
    }



    public function execute()
    {
        $this->prepare();        

        $result = curl_exec($this->ch);
        
        if (curl_errno($this->ch)) {
            throw new ConnectionException(curl_error($this->ch), curl_errno($this->ch));
        }

        list($header, $body) = explode("\r\n\r\n", $result, 2);
        
        $response = new Response();
        $response->setData($body);
        $response->setHeaders($this->parseHeader($header));
        $response->setRequest($this);

        return $response;
    }

}
