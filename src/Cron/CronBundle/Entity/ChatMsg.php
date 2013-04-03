<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\ChatMsg
 *
 * @ORM\Table(name="chat_msg")
 * @ORM\Entity
 */
class ChatMsg extends AbstractEntity {
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
     * @var string $msg_text
     *
     * @ORM\Column(name="msg_text", type="string", length=5000, nullable=false)
     */
    protected $msg_text;

    /**
     * @var \DateTime $msg_date
     *
     * @ORM\Column(name="msg_date", type="datetime", nullable=false)
     */
    protected $msg_date;


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
     * Set msg_text
     *
     * @param string $msgText
     * @return ChatMsg
     */
    public function setMsgText($msgText)
    {
        $this->msg_text = $msgText;
    
        return $this;
    }

    /**
     * Get msg_text
     *
     * @return string 
     */
    public function getMsgText()
    {
        return $this->makeClickableLinks($this->msg_text);
    }

    /**
     * Set msg_date
     *
     * @param \DateTime $msgDate
     * @return ChatMsg
     */
    public function setMsgDate($msgDate)
    {
        $this->msg_date = $msgDate;
    
        return $this;
    }

    /**
     * Get msg_date
     *
     * @return \DateTime 
     */
    public function getMsgDate()
    {
        return $this->msg_date;
    }

    /**
     * Set chat
     *
     * @param Cron\CronBundle\Entity\Chat $chat
     * @return ChatMsg
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
     * @return ChatMsg
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