<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Cron\CronBundle\Entity\User;

/**
 * Cron\CronBundle\Entity\Notepad
 *
 * @ORM\Table(name="notepad")
 * @ORM\Entity
 */
class Notepad
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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

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
     * Set text
     *
     * @param string $text
     * @return Notepad
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
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Notepad
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
     * @return Notepad
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