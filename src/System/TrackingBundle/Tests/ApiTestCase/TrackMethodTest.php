<?php
namespace System\TrackingBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrackMethodTest extends WebTestCase
{
    private $positions = array(
    	array(
    	   'date' => '20131118092109',
    	   'lat' => 50.0636422,
    	   'lng' => 19.9371549,
    	   'speed' => 10.960000,
    	   'alt' => 210.700000,
    	   'course' => 144.610000       
        )
                    
    );
    
    public function getPositions(){
        $data = array(
        	'date' => array(),
        	'lat' => array(),
        	'lng' => array(),
        	'speed' => array(),
        	'alt' => array(),
        	'course' => array()
        );
        
        foreach($this->positions as $i => $position){
            foreach($position as $key => $val){
                $data[$key][$i] = $val;
            }
        }
        
        return $data;
    }
    
    public function testTrack(){
        $client = static::createClient();
        
        /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $client->request('POST', '/pl/track', $this->getPositions(), array(), array(
        	'HTTP_API_KEY' => 'c6592eaca373198fdedfab7402e507f4'
        ));
        
        // assume success
        $this->assertEquals(
                1,
                $crawler->text()
        );
    }
}