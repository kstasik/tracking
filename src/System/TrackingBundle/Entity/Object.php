<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\ObjectRepository")
 * @ORM\Table(name="object")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Object
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank
     * @Expose
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="objects")
     * @ORM\JoinTable(name="user_to_object",
     *      joinColumns={@ORM\JoinColumn(name="object_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="Position", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $positions;

    /**
     * @ORM\Column(length=40, unique=true)
     */
    protected $api_key;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="device")
     */
    protected $messages;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->positions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Object
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set api_key
     *
     * @param string $apiKey
     * @return Object
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
     * Add positions
     *
     * @param \System\TrackingBundle\Entity\Position $positions
     * @return Object
     */
    public function addPosition(\System\TrackingBundle\Entity\Position $positions)
    {
        $this->positions[] = $positions;

        return $this;
    }

    /**
     * Remove positions
     *
     * @param \System\TrackingBundle\Entity\Position $positions
     */
    public function removePosition(\System\TrackingBundle\Entity\Position $positions)
    {
        $this->positions->removeElement($positions);
    }

    /**
     * Get positions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Add users
     *
     * @param \System\TrackingBundle\Entity\User $users
     * @return Object
     */
    public function addUser(\System\TrackingBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \System\TrackingBundle\Entity\User $users
     */
    public function removeUser(\System\TrackingBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add messages
     *
     * @param \System\TrackingBundle\Entity\Message $messages
     * @return Object
     */
    public function addMessage(\System\TrackingBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \System\TrackingBundle\Entity\Message $messages
     */
    public function removeMessage(\System\TrackingBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
