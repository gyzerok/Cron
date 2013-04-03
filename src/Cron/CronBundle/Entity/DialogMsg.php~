<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\DialogMsg
 *
 * @ORM\Table(name="dialog_msg")
 * @ORM\Entity
 */
class DialogMsg extends AbstractEntity {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Dialog
     *
     * @ORM\ManyToOne(targetEntity="Dialog")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dialog", referencedColumnName="id", nullable=false)
     * })
     */
    protected $dialog;

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
     * @var boolean $read_flag
     *
     * @ORM\Column(name="read_flag", type="boolean")
     */
    protected $read_flag;

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
     * @return DialogMsg
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
     * Set read_flag
     *
     * @param boolean $readFlag
     * @return DialogMsg
     */
    public function setReadFlag($readFlag)
    {
        $this->read_flag = $readFlag;
    
        return $this;
    }

    /**
     * Get read_flag
     *
     * @return boolean 
     */
    public function getReadFlag()
    {
        return $this->read_flag;
    }

    /**
     * Set msg_date
     *
     * @param \DateTime $msgDate
     * @return DialogMsg
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
     * Set dialog
     *
     * @param Cron\CronBundle\Entity\Dialog $dialog
     * @return DialogMsg
     */
    public function setDialog(\Cron\CronBundle\Entity\Dialog $dialog)
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
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return DialogMsg
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