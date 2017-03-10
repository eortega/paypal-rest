<?php
namespace PaypalRest\httpclient;


class CurlClient
{

    protected $headerOptions      = array();

    /**
     * Defines HTTP method that will be used in rerquets
     * POST (By default), GET, PUT, DELETE, PATH
     *
     * @var string $method
     */
    protected $method             = 'POST';

    /**
     * Uses to store header options sended un curl request
     * @var array headersList
     */
    protected $headersList        = array();
    protected $curlOptions;

    /**
     * Defines if params will be sent in json format
     * @var bool $isJson
     */
    protected $isJson             = true;
    /**
     *
     * @var string $endPoint
     */
    protected $endPoint;
    protected $http_query = false;



    /**
     * CurlClient constructor.
     * @param array $curlOptions
     * @param array $headerOptions
     */
    public function __construct($curlOptions = array(), $headerOptions = array())
    {
        $headerOptions = empty($headerOptions) ? $this->getDefaultHeaders() : $headerOptions;

        if (empty($curlOptions)) {
            $curlOptions = $this->getDefaultCurlOptions();
        } else if (!empty($headerOptions)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $headerOptions;
        }

        $this->setHeaderOptions($headerOptions);
        $this->setCurlOptions($curlOptions);
        $this->setHeadersList($headerOptions);

    }


    /**
     * Executes curl
     *
     * @return array $response response data
     */
    public function send()
    {
        error_log("Sending\n", 3, '/tmp/curl_client.log');
        // Init new CURL session
        $ch = curl_init();
        foreach ($this->curlOptions as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $http_response = curl_exec($ch);
        error_log("RAW Response\n" . print_r($http_response, true) . "\n", 3, '/tmp/curl_client.log');
        $http_status   = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $http_error    = curl_error($ch);
        //error_log("CURL_INFO\n" . print_r(  curl_getinfo($ch), true) . "\n", 3, '/tmp/curl_client.log');
        $header_size   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        /**
         * Generate response data to an Object Class
         * @var HttpResponse
         */
        $response = new HttpResponse($http_response, $header_size, $this->curlOptions[CURLOPT_HEADER]);
        if (200 <= $http_status && 300 > $http_status) {
            return array(   'Success' => '1',
                            'Status' => $http_status,
                            'Response' => $response);
        } else {
            return array(
                        'Success' => '0',
                        'Status' => (empty($http_error) ? $http_status : $http_error),
                        'Response' => $response);
        }

    }

    /**
     * Setting for all variables which curl will send.
     *
     * @param  String $service url endpoint for access api
     * @param  array  $params  variabel requirement
     */
    public function setup($service, $params = array())
    {
        $url = $this->endPoint . $service;
        error_log(print_r($url, true) . "\n", 3, '/tmp/curl_client.log');

        if ('GET' == $this->method) {
            error_log("GET request\n", 3, '/tmp/curl_client.log');
            $url .= !empty($params) ? '?' . http_build_query($params) : '';
        } else {

            if ('POST' == $this->method) {
                error_log("Post request\n", 3, '/tmp/curl_client.log');
                $this->setCurlOptions(array(CURLOPT_POST => true));
            } else {
                $this->setCurlOptions(array(CURLOPT_CUSTOMREQUEST => $this->method));
            }

            if ($this->isJson) {
                error_log("Send json\n", 3, '/tmp/curl_client.log');
                $this->setCurlOptions(array(CURLOPT_POSTFIELDS => json_encode($params)));
                $this->setHeader('Content-Type', 'application/json');
            } else {
                error_log("Not json\n", 3, '/tmp/curl_client.log');
                if(!empty($params)) {
                    if ($this->http_query) {
                        error_log("Send in url query\n", 3, '/tmp/curl_client.log');
                        $url .= '?' . http_build_query($params);
                    } else {
                        error_log("Post request\n", 3, '/tmp/curl_client.log');
                        $this->setCurlOptions(array(CURLOPT_POSTFIELDS => $params));
                    }
                }
            }
        }



        $this->setCurlOptions(array(CURLOPT_URL => $url));
    }



    /**
     * @param array $headerOptions
     */
    public function setHeadersList($headerOptions = array ())
    {

        if(!empty($headerOptions) && is_array($headerOptions)) {
            foreach ($headerOptions as $option) {
                $headerOption = explode(':', $option);
                array_push($this->headersList, $headerOption[0]);
            }
        }

    }

    /**
     * setting header for curlopt_header
     * @param string $nameHeader header name
     * @param string $value      value of header name
     */
    public function setHeader($nameHeader = '', $value = '')
    {
        $headers = $this->headerOptions;

        if (!in_array($nameHeader, $this->headersList)) {
            array_push($headers, $nameHeader . ': ' . $value);
            array_push($this->headersList, $nameHeader);
        } else {
            // Updates header value
            $key_index          = array_search($nameHeader, $this->headersList);
            $headers[$key_index] = $nameHeader . ': ' . $value;
        }

        $this->setHeaderOptions($headers);
        $this->curlOptions = array(CURLOPT_HTTPHEADER => $headers) + $this->curlOptions;
    }

    /**
     * Sets CURL options for all requests.
     *
     * @param array $curlOptions CURL options.
     */
    protected function setCurlOptions($curlOptions)
    {
        if (empty($this->curlOptions)) {
            if (!array_key_exists(CURLOPT_FOLLOWLOCATION, $curlOptions)) {
                $curlOptions[CURLOPT_FOLLOWLOCATION] = 1;
            }
            $curlOptions[CURLOPT_RETURNTRANSFER] = 1;
            $this->curlOptions                   = $curlOptions;
        } else {
            $this->curlOptions = $curlOptions + $this->curlOptions;
        }
    }

    /**
     * Setting method http for curl
     * @param string $method
     */
    public function setMethod($method = 'POST')
    {
        $this->method = $method;
    }

    /**
     * Setting method http for curl
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Setting json boolean false or true
     * @param Boolean $json
     */
    public function setIsJson($json)
    {
        $this->isJson = $json;
    }

    /**
     * @param String $url
     */
    public function setEndPoint($url)
    {
        $this->endPoint = $url;
    }

    /**
     * @param array $heaerOptions
     */
    protected function setHeaderOptions($heaerOptions = array ())
    {
        $this->headerOptions = $heaerOptions;
    }

    /**
     * Return  basic header options
     * @return array
     */
    private function getDefaultHeaders()
    {
        return array(
            'Accept: */*',
            'Cache-Control: max-age=0',
            'Accept-Charset: utf-8;q=0.7,*;q=0.7',
            'Pragma: no-cache',
            );
    }

    private function getDefaultCurlOptions()
    {
        return array(
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            //CURLOPT_VERBOSE        => true,
            /*CURLOPT_SSL_CIPHER_LIST=> 'TLSv1',*/
            //CURLOPT_SSLVERSION        => 3
        );
    }
}