<?php

namespace Pixelarbeit\Http;



class Response
{
    private $headers;
    private $data;
    private $request;



    public function __construct()
    {

    }



    public function headers()
    {
        return $this->headers;
    }
    
    
    
    public function data()
    {
        return $this->data;
    }


    
    public function request()
    {
        return $this->request;
    }
    
    
    
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    
    
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
}
