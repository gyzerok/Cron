<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity
 */
class Question
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
     * @var string $text
     *
     * @ORM\Column(name="text", type="string", length=130, nullable=false)
     */
    private $text;

    /**
     * @var boolean $private
     *
     * @ORM\Column(name="private", type="boolean", nullable=false)
     */
    private $private;

    /**
     * @var integer $boundary
     *
     * @ORM\Column(name="boundary", type="integer", nullable=false)
     */
    private $boundary;

    /**
     * @var integer $status
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime $datetime
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    private $datetime;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="userLikes",
     *     joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $likes
     */
    private $likes;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="userSpams",
     *     joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $spams
     */
    private $spams;

    /**
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $city;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     * })
     */
    private $country;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $category;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var boolean $isSpam
     *
     * @ORM\Column(name="is_spam", type="boolean", nullable=false)
     */
    private $isSpam;



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
     * @return Question
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
     * Set private
     *
     * @param boolean $private
     * @return Question
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    
        return $this;
    }

    /**
     * Get private
     *
     * @return boolean 
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set boundary
     *
     * @param integer $boundary
     * @return Question
     */
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
    
        return $this;
    }

    /**
     * Get boundary
     *
     * @return integer 
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Question
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Question
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
     * Set likes
     *
     * @param integer $likes
     * @return Question
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    
        return $this;
    }

    /**
     * Get likes
     *
     * @return integer 
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * Set spams
     *
     * @param integer $spams
     * @return Question
     */
    public function setSpams($spams)
    {
        $this->spams = $spams;
    
        return $this;
    }

    /**
     * Get spams
     *
     * @return integer 
     */
    public function getSpams()
    {
        return $this->spams;
    }

    /**
     * Set state
     *
     * @param Cron\CronBundle\Entity\State $state
     * @return Question
     */
    public function setState(\Cron\CronBundle\Entity\State $state = null)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return Cron\CronBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set city
     *
     * @param Cron\CronBundle\Entity\City $city
     * @return Question
     */
    public function setCity(\Cron\CronBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return Cron\CronBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param Cron\CronBundle\Entity\Country $country
     * @return Question
     */
    public function setCountry(\Cron\CronBundle\Entity\Country $country = null)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return Cron\CronBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set category
     *
     * @param Cron\CronBundle\Entity\Category $category
     * @return Question
     */
    public function setCategory(\Cron\CronBundle\Entity\Category $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return Cron\CronBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return Question
     */
    public function setUser(\Cron\CronBundle\Entity\User $user = null)
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

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->spams = new ArrayCollection();
        $this->datetime = new \DateTime();
        $this->status = 0;
        $this->isSpam = false;
    }

    /**
     * Add likes
     *
     * @param Cron\CronBundle\Entity\User $likes
     * @return Question
     */
    public function addLike(\Cron\CronBundle\Entity\User $likes)
    {
        $this->likes[] = $likes;

        return $this;
    }

    /**
     * Remove likes
     *
     * @param Cron\CronBundle\Entity\User $likes
     */
    public function removeLike(\Cron\CronBundle\Entity\User $likes)
    {
        $this->likes->removeElement($likes);
    }

    /**
     * Add spams
     *
     * @param Cron\CronBundle\Entity\User $spams
     * @return Question
     */
    public function addSpam(\Cron\CronBundle\Entity\User $spams)
    {
        $this->spams[] = $spams;

        return $this;
    }

    /**
     * Remove spams
     *
     * @param Cron\CronBundle\Entity\User $spams
     */
    public function removeSpam(\Cron\CronBundle\Entity\User $spams)
    {
        $this->spams->removeElement($spams);
    }

    /**
     * Set isSpam
     *
     * @param boolean $isSpam
     * @return Question
     */
    public function setIsSpam($isSpam)
    {
        $this->isSpam = $isSpam;
    
        return $this;
    }

    /**
     * Get isSpam
     *
     * @return boolean 
     */
    public function getIsSpam()
    {
        return $this->isSpam;
    }
}