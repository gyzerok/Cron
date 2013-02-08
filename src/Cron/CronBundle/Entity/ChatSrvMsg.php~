<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\ChatSrvMsg
 *
 * @ORM\Table(name="chat_srvmsg")
 * @ORM\Entity
 */
class ChatSrvMsg {
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
     *   @ORM\JoinColumn(name="chat", referencedColumnName="id", nullable=true)
     * })
     */
    protected $chat;

    /**
     * @var Dialog
     *
     * @ORM\ManyToOne(targetEntity="Dialog")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dialog", referencedColumnName="id", nullable=true)
     * })
     */
    protected $dialog;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="to_user", referencedColumnName="id", nullable=false)
     * })
     */
    protected $to_user;

    /**
     * @var string $msg_text_id
     *
     * @ORM\Column(name="msg_text_id", type="integer", nullable=false)
     */
    protected $msg_text_id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="about_user", referencedColumnName="id", nullable=true)
     * })
     */
    protected $about_user;

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
     * Set msg_date
     *
     * @param \DateTime $msgDate
     * @return ChatSrvMsg
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
     * @return ChatSrvMsg
     */
    public function setChat(\Cron\CronBundle\Entity\Chat $chat = null)
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
     * Set dialog
     *
     * @param Cron\CronBundle\Entity\Dialog $dialog
     * @return ChatSrvMsg
     */
    public function setDialog(\Cron\CronBundle\Entity\Dialog $dialog = null)
    {
        $this->dialog = $dialog;
    
        return $this;
    }

    /**
     * Get dialog
     *
     * @return Cron\CronBundle\Entity\Dialog 
     */
    public function getDialog()
    {
        return $this->dialog;
    }

    /**
     * Set to_user
     *
     * @param Cron\CronBundle\Entity\User $toUser
     * @return ChatSrvMsg
     */
    public function setToUser(\Cron\CronBundle\Entity\User $toUser)
    {
        $this->to_user = $toUser;
    
        return $this;
    }

    /**
     * Get to_user
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getToUser()
    {
        return $this->to_user;
    }

    /**
     * Set about_user
     *
     * @param Cron\CronBundle\Entity\User $aboutUser
     * @return ChatSrvMsg
     */
    public function setAboutUser(\Cron\CronBundle\Entity\User $aboutUser = null)
    {
        $this->about_user = $aboutUser;
    
        return $this;
    }

    /**
     * Get about_user
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getAboutUser()
    {
        return $this->about_user;
    }

    /**
     * Set msg_text_id
     *
     * @param integer $msgTextId
     * @return ChatSrvMsg
     */
    public function setMsgTextId($msgTextId)
    {
        $this->msg_text_id = $msgTextId;
    
        return $this;
    }

    /**
     * Get msg_text_id
     *
     * @return integer 
     */
    public function getMsgTextId()
    {
        return $this->msg_text_id;
    }
}