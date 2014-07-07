<?php
namespace System\TrackingBundle\Tests\RestApiTestCase;

use Liip\FunctionalTestBundle\Test\WebTestCase as WebTestCase;

class DeviceTest extends WebTestCase
{
    public function setUp()
    {
        $this->client = static::createClient();
    }
    
    public function testCreateAction(){
        $this->client->request(
            'POST',
            '/api/v1/devices.json',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"username":"kacper","password":"qwas1212","system":"android","reg_id":"1324131234432423423423sadsdfsdfsdf234234234234234"}'
        );

        echo $this->client->getResponse();
        
        // todo: write test
    }
}