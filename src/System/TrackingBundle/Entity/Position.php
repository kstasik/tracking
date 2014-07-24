<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\PositionRepository")
 * @ORM\Table(name="object_position")
 */
class Position
{
    /**
     * position types
     */
    const TYPE_NEW = 0;

    const TYPE_TRIP_START = 1;
    const TYPE_TRIP = 2;
    const TYPE_TRIP_END = 3;
    
    const TYPE_PARKING = 4;
    
    /**
     * position types used for classifing positions
     */
    const TYPE_PARKING_CONTEXT = 4;
    const TYPE_PARKING_CANDIDATE = 5;
    
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Object", inversedBy="positions")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $object;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_created;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_fixed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_satellite;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6)
     */
    protected $speed;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6, nullable=true)
     */
    protected $altitude;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6, nullable=true)
     */
    protected $course;
   
    /**
     * @ORM\Column(type="smallint")
     */
    protected $type = self::TYPE_NEW;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="device")
     */
    protected $messages;

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
     * Set latitude
     *
     * @param float $latitude
     * @return Position
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Position
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set date_created
     *
     * @param \DateTime $dateCreated
     * @return Position
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
     * Set date_satellite
     *
     * @param \DateTime $dateSatellite
     * @return Position
     */
    public function setDateSatellite($dateSatellite)
    {
        $this->date_satellite = $dateSatellite;

        return $this;
    }

    /**
     * Get date_satellite
     *
     * @return \DateTime 
     */
    public function getDateSatellite()
    {
        return $this->date_satellite;
    }

    /**
     * Set speed
     *
     * @param float $speed
     * @return Position
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return float 
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set alt
     *
     * @param float $alt
     * @return Position
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return float 
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set cource
     *
     * @param float $cource
     * @return Position
     */
    public function setCource($cource)
    {
        $this->cource = $cource;

        return $this;
    }

    /**
     * Get cource
     *
     * @return float 
     */
    public function getCource()
    {
        return $this->cource;
    }

    /**
     * Set altitude
     *
     * @param float $altitude
     * @return Position
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;

        return $this;
    }

    /**
     * Get altitude
     *
     * @return float 
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * Set course
     *
     * @param float $course
     * @return Position
     */
    public function setCourse($course)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return float 
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set object
     *
     * @param \System\TrackingBundle\Entity\Object $object
     * @return Position
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
     * Set date_fixed
     *
     * @param \DateTime $dateFixed
     * @return Position
     */
    public function setDateFixed($dateFixed)
    {
        $this->date_fixed = $dateFixed;

        return $this;
    }

    /**
     * Get date_fixed
     *
     * @return \DateTime 
     */
    public function getDateFixed()
    {
        return $this->date_fixed;
    }

    /**
     * Set type
     *
     * @param int $type
     * @return Position
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int 
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add messages
     *
     * @param \System\TrackingBundle\Entity\Message $messages
     * @return Position
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
