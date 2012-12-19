<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cron\CronBundle\Entity\User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=45, nullable=false)
     */
    private $username;

    /**
     * @var string $userpass
     *
     * @ORM\Column(name="userpass", type="string", length=45, nullable=false)
     */
    private $userpass;

    /**
     * @var \DateTime $regDate
     *
     * @ORM\Column(name="reg_date", type="datetime", nullable=false)
     */
    private $regDate;

    /**
     * @var \DateTime $lastVisit
     *
     * @ORM\Column(name="last_visit", type="datetime", nullable=false)
     */
    private $lastVisit;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $city;

    /**
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     * })
     */
    private $country;



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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set userpass
     *
     * @param string $userpass
     * @return User
     */
    public function setUserpass($userpass)
    {
        $this->userpass = $userpass;
    
        return $this;
    }

    /**
     * Get userpass
     *
     * @return string 
     */
    public function getUserpass()
    {
        return $this->userpass;
    }

    /**
     * Set regDate
     *
     * @param \DateTime $regDate
     * @return User
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;
    
        return $this;
    }

    /**
     * Get regDate
     *
     * @return \DateTime 
     */
    public function getRegDate()
    {
        return $this->regDate;
    }

    /**
     * Set lastVisit
     *
     * @param \DateTime $lastVisit
     * @return User
     */
    public function setLastVisit($lastVisit)
    {
        $this->lastVisit = $lastVisit;
    
        return $this;
    }

    /**
     * Get lastVisit
     *
     * @return \DateTime 
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     * Set city
     *
     * @param Cron\CronBundle\Entity\City $city
     * @return User
     */
    public function setCity(\Cron\CronBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return Cron\CronBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param Cron\CronBundle\Entity\State $state
     * @return User
     */
    public function setState(\Cron\CronBundle\Entity\State $state = null)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return Cron\CronBundle\Entity\State 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set country
     *
     * @param Cron\CronBundle\Entity\Country $country
     * @return User
     */
    public function setCountry(\Cron\CronBundle\Entity\Country $country = null)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return Cron\CronBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function __toString()
    {
        return $this->getUsername();
    }
}