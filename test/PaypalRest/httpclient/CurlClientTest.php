<?php
use PaypalRest\httpclient\CurlClient;
use PaypalRest\httpclient\HttpResponse;

class CurlClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  CurlClient $client
     */
    protected $client;

    public function setUp()
    {
        $this->client = new CurlClient();
    }

    public function testObject()
    {
        // $class = get_class($this->curlCliente);
        //echo $class;
        $this->client->setMethod('POST');
        $this->assertEquals('POST', $this->client->getMethod());
    }

    public function testGetRequest()
    {
        $this->client->setEndPoint('https://www.osom.com/api/');
        $this->client->setMethod('GET');
        $this->assertEquals('GET', $this->client->getMethod());
        $this->client->setup('address', array('postcode' => 11560));

        $r = $this->client->send();
        $this->assertEquals(200, $r['Status']);
    }

    public function testPostRequest()
    {
        $this->client->setEndPoint('https://www.osom.com/api/');
        $this->client->setMethod('POST');
        $this->client->setIsJson(false);
        $this->client->setHeader('Content-Type', 'application/json');
        $this->client->setup('customer/login', array());

        $r = $this->client->send();
        $this->assertEquals(400, $r['Status']);
        $this->assertEquals(4, $r['Response']->body->error);
    }

}