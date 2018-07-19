<?php

namespace Omnipay\Mollie\Test\Message;

use Omnipay\Mollie\Message\CompletePurchaseRequest;
use Omnipay\Tests\TestCase;

class CompletePurchaseRequestTest extends TestCase
{
    use AssertRequestTrait;

    /**
     * @var \Omnipay\Mollie\Message\CompletePurchaseRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array(
            'apiKey' => 'mykey',
        ));

        $this->getHttpRequest()->request->replace(array(
            'id' => 'tr_Qzin4iTWrU',
        ));
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The transactionReference parameter is required
     */
    public function testGetDataWithoutIDParameter()
    {
        $this->getHttpRequest()->request->remove('id');

        $data = $this->request->getData();

        $this->assertEmpty($data);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame("tr_Qzin4iTWrU", $data['id']);
        $this->assertCount(1, $data);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CompletePurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertEqualRequest(new \GuzzleHttp\Psr7\Request("GET", "https://api.mollie.com/v2/payments/tr_Qzin4iTWrU"), $this->getMockClient()->getLastRequest());

        $this->assertInstanceOf('Omnipay\Mollie\Message\CompletePurchaseResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isOpen());
        $this->assertTrue($response->isPaid());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('tr_Qzin4iTWrU', $response->getTransactionReference());
    }

    public function testSendExpired()
    {
        $this->setMockHttpResponse('CompletePurchaseExpired.txt');
        $response = $this->request->send();

        $this->assertEqualRequest(new \GuzzleHttp\Psr7\Request("GET", "https://api.mollie.com/v2/payments/tr_Qzin4iTWrU"), $this->getMockClient()->getLastRequest());

        $this->assertInstanceOf('Omnipay\Mollie\Message\CompletePurchaseResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isPaid());
        $this->assertTrue($response->isExpired());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('tr_Qzin4iTWrU', $response->getTransactionReference());
    }
}
