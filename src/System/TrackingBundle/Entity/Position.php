<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="System\TrackingBundle\Entity\PositionRepository")
 * @ORM\Table(name="user_position")
 */
class Position
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="positions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_satellite;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6)
     */
    protected $speed;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6)
     */
    protected $altitude;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=6, nullable=true)
     */
    protected $course;

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
     * Set user
     *
     * @param \System\TrackingBundle\Entity\User $user
     * @return Position
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
}
