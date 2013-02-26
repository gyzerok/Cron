<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\Dialog
 *
 * @ORM\Table(name="dialog")
 * @ORM\Entity
 */
class Dialog {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user1", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user1;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user2", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user2;

    /**
     * @var boolean $del1
     *
     * @ORM\Column(name="del1", type="boolean")
     */
    protected $del1;

    /**
     * @var boolean $del2
     *
     * @ORM\Column(name="del2", type="boolean")
     */
    protected $del2;

    /**
     * @var boolean $spam1
     *
     * @ORM\Column(name="spam1", type="boolean")
     */
    protected $spam1;

    /**
     * @var boolean $spam2
     *
     * @ORM\Column(name="spam2", type="boolean")
     */
    protected $spam2;

    /**
     * @var boolean $ignore1
     *
     * @ORM\Column(name="ignore1", type="boolean")
     */
    protected $ignore1 = 0;

    /**
     * @var boolean $ignore2
     *
     * @ORM\Column(name="ignore2", type="boolean")
     */
    protected $ignore2 = 0;

    /**
     * @var boolean $open1
     *
     * @ORM\Column(name="open1", type="boolean")
     */
    protected $open1;

    /**
     * @var boolean $open2
     *
     * @ORM\Column(name="open2", type="boolean")
     */
    protected $open2;

    /**
     * @var \DateTime $start_date
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    protected $start_date;


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
     * Set del1
     *
     * @param boolean $del1
     * @return Dialog
     */
    public function setDel1($del1)
    {
        $this->del1 = $del1;
    
        return $this;
    }

    /**
     * Get del1
     *
     * @return boolean 
     */
    public function getDel1()
    {
        return $this->del1;
    }

    /**
     * Set del2
     *
     * @param boolean $del2
     * @return Dialog
     */
    public function setDel2($del2)
    {
        $this->del2 = $del2;
    
        return $this;
    }

    /**
     * Get del2
     *
     * @return boolean 
     */
    public function getDel2()
    {
        return $this->del2;
    }

    /**
     * Set spam1
     *
     * @param boolean $spam1
     * @return Dialog
     */
    public function setSpam1($spam1)
    {
        $this->spam1 = $spam1;
    
        return $this;
    }

    /**
     * Get spam1
     *
     * @return boolean 
     */
    public function getSpam1()
    {
        return $this->spam1;
    }

    /**
     * Set spam2
     *
     * @param boolean $spam2
     * @return Dialog
     */
    public function setSpam2($spam2)
    {
        $this->spam2 = $spam2;
    
        return $this;
    }

    /**
     * Get spam2
     *
     * @return boolean 
     */
    public function getSpam2()
    {
        return $this->spam2;
    }

    /**
     * Set start_date
     *
     * @param \DateTime $startDate
     * @return Dialog
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;
    
        return $this;
    }

    /**
     * Get start_date
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set user1
     *
     * @param Cron\CronBundle\Entity\User $user1
     * @return Dialog
     */
    public function setUser1(\Cron\CronBundle\Entity\User $user1)
    {
        $this->user1 = $user1;
    
        return $this;
    }

    /**
     * Get user1
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * Set user2
     *
     * @param Cron\CronBundle\Entity\User $user2
     * @return Dialog
     */
    public function setUser2(\Cron\CronBundle\Entity\User $user2)
    {
        $this->user2 = $user2;
    
        return $this;
    }

    /**
     * Get user2
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getUser2()
    {
        return $this->user2;
    }

    /**
     * Set open1
     *
     * @param boolean $open1
     * @return Dialog
     */
    public function setOpen1($open1)
    {
        $this->open1 = $open1;
    
        return $this;
    }

    /**
     * Get open1
     *
     * @return boolean 
     */
    public function getOpen1()
    {
        return $this->open1;
    }

    /**
     * Set open2
     *
     * @param boolean $open2
     * @return Dialog
     */
    public function setOpen2($open2)
    {
        $this->open2 = $open2;
    
        return $this;
    }

    /**
     * Get open2
     *
     * @return boolean 
     */
    public function getOpen2()
    {
        return $this->open2;
    }

    /**
     * Set ignore1
     *
     * @param boolean $ignore1
     * @return Dialog
     */
    public function setIgnore1($ignore1)
    {
        $this->ignore1 = $ignore1;
    
        return $this;
    }

    /**
     * Get ignore1
     *
     * @return boolean 
     */
    public function getIgnore1()
    {
        return $this->ignore1;
    }

    /**
     * Set ignore2
     *
     * @param boolean $ignore2
     * @return Dialog
     */
    public function setIgnore2($ignore2)
    {
        $this->ignore2 = $ignore2;
    
        return $this;
    }

    /**
     * Get ignore2
     *
     * @return boolean 
     */
    public function getIgnore2()
    {
        return $this->ignore2;
    }
}