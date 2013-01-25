<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\ChatInvite
 *
 * @ORM\Table(name="chat_invite")
 * @ORM\Entity
 */
class ChatInvite {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Chat
     *
     * @ORM\ManyToOne(targetEntity="Chat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chat", referencedColumnName="id", nullable=false)
     * })
     */
    protected $chat;

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
     * @return ChatInvite
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
     * Set chat
     *
     * @param Cron\CronBundle\Entity\Chat $chat
     * @return ChatInvite
     */
    public function setChat(\Cron\CronBundle\Entity\Chat $chat)
    {
        $this->chat = $chat;
    
        return $this;
    }

    /**
     * Get chat
     *
     * @return Cron\CronBundle\Entity\Chat 
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * Set user1
     *
     * @param Cron\CronBundle\Entity\User $user1
     * @return ChatInvite
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
     * @return ChatInvite
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