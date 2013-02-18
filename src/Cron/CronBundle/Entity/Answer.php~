<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cron\CronBundle\Entity\Answer
 *
 * @ORM\Table(name="answer")
 * @ORM\Entity
 */
class Answer
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
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var boolean $status
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime $pubDate
     *
     * @ORM\Column(name="pub_date", type="datetime", nullable=false)
     */
    private $pubDate;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="userLikes1",
     *     joinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $likes
     */
    private $likes;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="userSpams1",
     *     joinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $spams
     */
    private $spams;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     * })
     */
    private $question;

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
     * @return Answer
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
     * Set status
     *
     * @param boolean $status
     * @return Answer
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set pubDate
     *
     * @param \DateTime $pubDate
     * @return Answer
     */
    public function setPubDate($pubDate)
    {
        $this->pubDate = $pubDate;
    
        return $this;
    }

    /**
     * Get pubDate
     *
     * @return \DateTime 
     */
    public function getPubDate()
    {
        return $this->pubDate;
    }

    /**
     * Set likes
     *
     * @param integer $likes
     * @return Answer
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
     * @return Answer
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
     * Set question
     *
     * @param Cron\CronBundle\Entity\Question $question
     * @return Answer
     */
    public function setQuestion(\Cron\CronBundle\Entity\Question $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return Cron\CronBundle\Entity\Question 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return Answer
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
        $this->setPubDate(new \DateTime());
        $this->setStatus(true);
    }

    /**
     * Add likes
     *
     * @param Cron\CronBundle\Entity\User $likes
     * @return Answer
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
     * @return Answer
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
}