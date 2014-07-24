<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\MessageRepository")
 * @ORM\Table(name="device_message")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\ManyToOne(targetEntity="Object", inversedBy="messages")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $object = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="Position", inversedBy="messages")
     * @ORM\JoinColumn(name="position_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $position = null;

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

    /**
     * Set object
     *
     * @param \System\TrackingBundle\Entity\Object $object
     * @return Message
     */
    public function setObject(\System\TrackingBundle\Entity\Object $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return \System\TrackingBundle\Entity\Object 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set position
     *
     * @param \System\TrackingBundle\Entity\Position $position
     * @return Message
     */
    public function setPosition(\System\TrackingBundle\Entity\Position $position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return \System\TrackingBundle\Entity\Position 
     */
    public function getPosition()
    {
        return $this->position;
    }
}
