<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use System\TrackingBundle\Entity\Position;

class ObjectController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Object');

        return $this->render('SystemTrackingBundle:Object:index.html.twig', array('objects' => $em->getObjectCollection($user)));
    }
    
    /**
     * Tracking api method
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function trackAction(Request $request){
        $em = $this->get('doctrine')->getManager();
        
        // get api key
        $object = $em->getRepository('SystemTrackingBundle:Object')->findOneBy(array('api_key' => $request->headers->get('api-key')));
        if(!$object){
            return new Response('-2');
        }
        
        // process request
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $attr = array('date','lat','lng','speed','alt','course');
            $data = array();
            
            for($i = 0; $i < count($post['date']); $i++){
                foreach($attr as $key){
                    if(!isset($post[$key][$i])){
                        return new Response('-1');
                    }
                }
                
                preg_match_all('/([0-9]{2})/i', $post['date'][$i], $m);
                $time = mktime( $m[0][3], $m[0][4], $m[0][5], $m[0][1], $m[0][0], $m[0][2] );
                $sat  = new \DateTime();
                $sat->setTimestamp($time);
                
                $position = new Position;
                
                $position
                    ->setObject($object)
                    ->setLatitude($post['lat'][$i])
                    ->setLongitude($post['lng'][$i])
                    ->setDateCreated(new \DateTime())
                    ->setDateSatellite($sat)
                    ->setSpeed($post['speed'][$i])
                    ->setAltitude($post['alt'][$i])
                    ->setCourse($post['course'][$i]);
                
                $em->persist($position);
                $em->flush();
            }

            return new Response('1');
            
        }
        
        return new Response('0');
    }
}