<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\Feedback
 *
 * @ORM\Table(name="feedback")
 * @ORM\Entity
 */
class Feedback {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var string $type
     *
     * @ORM\Column(name="_type", type="string", length=64, nullable=false)
     */
    private $type;

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var boolean $answered
     *
     * @ORM\Column(name="answered", type="boolean", nullable=false)
     */
    private $answered = 0;

    /**
     * @var \DateTime $datetime
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    private $datetime;


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
     * Set type
     *
     * @param string $type
     * @return Feedback
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Feedback
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set answered
     *
     * @param boolean $answered
     * @return Feedback
     */
    public function setAnswered($answered)
    {
        $this->answered = $answered;
    
        return $this;
    }

    /**
     * Get answered
     *
     * @return boolean 
     */
    public function getAnswered()
    {
        return $this->answered;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Feedback
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return Feedback
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