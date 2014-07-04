<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\ObjectRepository")
 * @ORM\Table(name="object")
 * @ORM\HasLifecycleCallbacks()
 */
class Object
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(length=255)
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
}
