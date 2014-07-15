<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="device_message")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class Message
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="messages")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     */
    protected $device;

    /**
     * @ORM\Column(type="datetime")
     * @Expose
     */
    protected $date_created;

    /**
     * @ORM\Column(length=40)
     * @Expose
     */
    private $action;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $request;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $response;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date_created
     *
     * @param \DateTime $dateCreated
     * @return Message
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get date_created
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Message
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set request
     *
     * @param string $request
     * @return Message
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return string 
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set response
     *
     * @param string $response
     * @return Message
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string 
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set device
     *
     * @param \System\TrackingBundle\Entity\Device $device
     * @return Message
     */
    public function setDevice(\System\TrackingBundle\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return \System\TrackingBundle\Entity\Device 
     */
    public function getDevice()
    {
        return $this->device;
    }
}
