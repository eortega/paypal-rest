<?php

class PaypalPlusTest extends \PHPUnit_Framework_TestCase
{
    protected $paypalClient;

    public function setUp()
    {
        $this->paypalClient = new PaypalRest\PaypalPlus();
    }

    public function testObject()
    {
        // $class = get_class($this->curlCliente);
        //echo $class;
        $this->assertEquals(10, $this->paypalClient->a);
    }

}