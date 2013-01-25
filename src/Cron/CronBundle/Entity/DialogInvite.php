<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\DialogInvite
 *
 * @ORM\Table(name="dialog_invite")
 * @ORM\Entity
 */
class DialogInvite {
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
     * @var \DateTime $invite_date
     *
     * @ORM\Column(name="invite_date", type="datetime", nullable=false)
     */
    protected $invite_date;


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
     * Set invite_date
     *
     * @param \DateTime $inviteDate
     * @return DialogInvite
     */
    public function setInviteDate($inviteDate)
    {
        $this->invite_date = $inviteDate;
    
        return $this;
    }

    /**
     * Get invite_date
     *
     * @return \DateTime 
     */
    public function getInviteDate()
    {
        return $this->invite_date;
    }

    /**
     * Set user1
     *
     * @param Cron\CronBundle\Entity\User $user1
     * @return DialogInvite
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
     * @return DialogInvite
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