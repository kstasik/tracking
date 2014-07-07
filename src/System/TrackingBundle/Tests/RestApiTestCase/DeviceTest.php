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
            '{"username":"username","password":"password","system":"ios","reg_id":"123"}'
        );

        echo $this->client->getResponse();
        
        // todo: write test
    }
}