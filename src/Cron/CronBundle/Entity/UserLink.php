<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Cron\CronBundle\Entity\User;

/**
 * Cron\CronBundle\Entity\UserLink
 *
 * @ORM\Table(name="user_link")
 * @ORM\Entity
 */
class UserLink
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user;

    /**
     * @var string $title
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     */
    private $title;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=512, nullable=false)
     */
    private $url;

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
     * Set title
     *
     * @param string $title
     * @return UserLink
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return UserLink
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return UserLink
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
     * @return UserLink
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