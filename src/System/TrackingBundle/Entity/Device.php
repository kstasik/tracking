<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\DeviceRepository")
 * @ORM\Table(name="device")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @ExclusionPolicy("all")
 */
class Device
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="devices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\ManyToMany(targetEntity="Object")
     * @ORM\JoinTable(name="device_to_object",
     *      joinColumns={@ORM\JoinColumn(name="device_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="object_id", referencedColumnName="id")}
     *      )
     * @Expose
     */
    private $objects;

    /**
     * @ORM\Column(length=40)
     */
    private $system;

    /**
     * @ORM\Column(type="text")
     * @Expose
     */
    private $reg_id;

    /**
     * @ORM\Column(length=40, unique=true)
     * @Expose
     */
    protected $api_key;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @ORM\PrePersist
     */
    public function preUpdate(){
        // generate api key
        if(!$this->getApiKey()){
            $this->setApiKey(md5(uniqid('', true)));
        }
    }

    /**
     * Set user
     *
     * @param \System\TrackingBundle\Entity\User $user
     * @return Device
     */
    public function setUser(\System\TrackingBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \System\TrackingBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add objects
     *
     * @param \System\TrackingBundle\Entity\Object $objects
     * @return Device
     */
    public function addObject(\System\TrackingBundle\Entity\Object $objects)
    {
        $this->objects[] = $objects;

        return $this;
    }

    /**
     * Remove objects
     *
     * @param \System\TrackingBundle\Entity\Object $objects
     */
    public function removeObject(\System\TrackingBundle\Entity\Object $objects)
    {
        $this->objects->removeElement($objects);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Set system
     *
     * @param string $system
     * @return Device
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set reg_id
     *
     * @param string $regId
     * @return Device
     */
    public function setRegId($regId)
    {
        $this->reg_id = $regId;

        return $this;
    }

    /**
     * Get reg_id
     *
     * @return string 
     */
    public function getRegId()
    {
        return $this->reg_id;
    }

    /**
     * Set api_key
     *
     * @param string $apiKey
     * @return Device
     */
    public function setApiKey($apiKey)
    {
        $this->api_key = $apiKey;

        return $this;
    }

    /**
     * Get api_key
     *
     * @return string 
     */
    public function getApiKey()
    {
        return $this->api_key;
    }
}
