<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Position", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $positions;

    /**
     * @ORM\Column(length=40, unique=true)
     */
    protected $api_key;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(){
        // generate api key
        if(!$this->getApiKey()){
            $this->setApiKey(md5(uniqid('', true)));
        }
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
     * Set api_key
     *
     * @param string $apiKey
     * @return User
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
     * Add positions
     *
     * @param \System\TrackingBundle\Entity\Position $positions
     * @return User
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
}
