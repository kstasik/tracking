<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use System\TrackingBundle\Entity\Position;
use System\TrackingBundle\Entity\Object;

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
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction(Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
        
        // create a task and give it some dummy data for this example
        $object = new Object();
        $object->addUser($user);
        
        $form = $this->createFormBuilder($object)
            ->add('name', 'text')
            ->add('api_key', 'text')
            ->add('save', 'submit')
            ->getForm();
        
        if($request->isMethod('POST')){
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                
                $em->persist($object);
                $em->flush();
                
                return $this->redirect($this->generateUrl('system_tracking_object'));
            }
        }
        
        return $this->render('SystemTrackingBundle:Object:new.html.twig', array(
                        'form' => $form->createView(),
        ));
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
        
        // last request
        file_put_contents($this->get('kernel')->getCacheDir().'/lastposition.log', print_r($_SERVER, true).PHP_EOL.print_r($request->request->all(), true));
        
        // process request
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $attr = array('date','lat','lng','speed','alt','course');
            $data = array();
            
            if(count($post['date']) == 0){
                return new Response('-3');
            }
            
            for($i = count($post['date'])-1; $i >= 0; $i--){
                foreach($attr as $key){
                    if(!isset($post[$key][$i])){
                        return new Response('-1');
                    }
                }
                
                // TODO: refactoring
                
                // 140714 9373100
                //  6071416165100
                //      0 9363200

                // fix stamp from tracking device
                preg_match('/^([0-9]{3,4})?('.date('y').'|0)?([0-9]{7,8})$/', $post['date'][$i], $m);
                
                $date = str_pad($m[1].$m[2],6, '0', STR_PAD_LEFT);
                $time = str_pad($m[3],8, '0', STR_PAD_LEFT);
                
                // fixed stamp
                $stamp = $date.$time;
                
                preg_match_all('/([0-9]{2})/i', $stamp, $m);
                
                // date not received - set currant day
                if($m[0][1] == 0 && $m[0][0] == 0 && $m[0][2] == 0){
                    $m[0][1] = date('m');
                    $m[0][0] = date('d');
                    $m[0][2] = date('Y');
                }
                
                $time = gmmktime( $m[0][3], $m[0][4], $m[0][5], $m[0][1], $m[0][0], $m[0][2] );
                $sat  = new \DateTime();
                $sat->setTimestamp($time);
                
                $position = new Position;
                
                $position
                    ->setObject($object)
                    ->setLatitude($post['lat'][$i])
                    ->setLongitude($post['lng'][$i])
                    ->setDateCreated(new \DateTime())
                    ->setDateSatellite($sat)
                    ->setDateFixed($sat)
                    ->setSpeed($post['speed'][$i]/100)
                    ->setAltitude($post['alt'][$i] == '999999999' ? null : $post['alt'][$i])
                    ->setCourse($post['course'][$i] == '999999999' ? null : $post['course'][$i]/100);
                
                $em->persist($position);
                $em->flush();
            }

            return new Response('1');
            
        }
        
        return new Response('0');
    }
}
