<?php
namespace PaypalRest\httpclient;


class HttpResponse
{

    /**
     * Body data description response
     * @var array
     */
    public $body = array();

    /**
     * Header data desscription response
     * @var array
     */
    public $header = array();

    /**
     * This function split response api to two part, body and header.
     *
     * @param String  $data
     * @param Integer  $header_size
     * @param Boolean $header
     */
    public function __construct($data, $header_size, $header = false)
    {
        if ($header) {
            $headers     = array();
            $header_text = substr($data, 0, $header_size);
            $array       = explode("\r\n", $header_text);
            $len         = count($array);

            for ($x = 0; $x < ($len - 2); $x++) {
                if ($x == 0) {
                    $headers['http_code'] = $array[$x];
                } else {
                    list($key, $value) = explode(': ', $array[$x]);
                    $headers[$key]     = $value;
                }
            }

            $this->header = $headers;
            $this->body   = json_decode(substr($data, $header_size));

            if (empty($this->body)) {
                $this->body = substr($data, $header_size);
            }

        } else {
            $this->body = json_decode($data);
        }
    }
}