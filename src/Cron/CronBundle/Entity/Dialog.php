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
}