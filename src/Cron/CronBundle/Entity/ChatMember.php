<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\ChatMember
 *
 * @ORM\Table(name="chat_member")
 * @ORM\Entity
 */
class ChatMember {
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
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user;


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
     * Set chat
     *
     * @param Cron\CronBundle\Entity\Chat $chat
     * @return ChatMember
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
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return ChatMember
     */
    public function setUser(\Cron\CronBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}