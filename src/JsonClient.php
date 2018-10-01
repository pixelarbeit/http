<?php

namespace Pixelarbeit\Http;

use Pixelarbeit\Http\Exceptions\InvalidResponseException;
use Pixelarbeit\Http\Exceptions\ConnectionException;



class JsonClient
{
    public $debug = false;

    private $bulkHandler;
    private $bulkRequests;



    public function __construct()
    {

    }



    /**
     * Sending request to given url
     * Throwing exception on connection error
     * @param  string $url Webservice request url
     * @return object      response
     */
    public function request($method, $url, $data = '', $headers = [])
    {
        $request = new Request();
        
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        $request->setMethod($method);
        $request->setHeaders($headers);        
        $request->setData($json);
        $request->setUrl($url);

        $request->addHeader('Content-Type: application/json; charset=utf-8');
        $request->addHeader('Content-Length: ' . strlen($json));

        $response = $request->execute();

        $json = json_decode($response->data());
        
        if ($json === null) {
            var_dump($response);
            throw new InvalidResponseException("No valid JSON", 1);
        }

        $response->setData($json);

        return $response;
    }



    public function get($url, $data = '', $headers = [])
    {
        return $this->request('GET', $url, $data = '', $headers = []);
    }



    public function put($url, $data = '', $headers = [])
    {
        return $this->request('PUT', $url, $data = '', $headers = []);
    }



    public function post($url, $data = '', $headers = [])
    {
        return $this->request('POST', $url, $data = '', $headers = []);
    }



    public function delete($url, $data = '', $headers = [])
    {
        return $this->request('DELETE', $url, $data = '', $headers = []);
    }


    
    public function initBulkRequest()
    {
        $this->bulkHandler = curl_multi_init();
        $this->bulkRequests = [];
    }



    public function addBulkRequest($method, $url, $data = '', $headers = [])
    {
        $ch = curl_init();
        $data = json_encode($data);
        $this->addJsonHeaders($headers, $data);
        $this->setOptions($ch, $method, $url, $data, $headers);

        curl_multi_add_handle($this->bulkHandler, $ch);
        $this->bulkRequests[] = $ch;
    }



    public function executeBulkRequest($jsonResponse = false)
    {
        $active = null;

        do {
            curl_multi_exec($this->bulkHandler, $running);
            usleep(100);
        } while($running > 0);

        $result = $this->getBulkResult($jsonResponse);

        curl_multi_close($this->bulkHandler);

        $this->bulkHandler = null;
        $this->bulkRequests = [];

        return  $result;
    }



    private function getBulkResult($jsonResponse)
    {
        $result = [];

        foreach ($this->bulkRequests as $key => $ch) {
            $json = json_decode(curl_multi_getcontent($ch));

            if ($json === null) {
                throw new InvalidResponseException("No valid JSON in bulk request", 1);
            }

            $result[$key] = $json;
            curl_multi_remove_handle($this->bulkHandler, $ch);
        }

        return $result;
    }
}
