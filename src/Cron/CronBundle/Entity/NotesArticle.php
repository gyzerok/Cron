<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\Article;

/**
 * Cron\CronBundle\Entity\NotesArticle
 *
 * @ORM\Table(name="notes_articles")
 * @ORM\Entity
 */
class NotesArticle
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
    private $user;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Article")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="article", referencedColumnName="id", nullable=false)
     * })
     */
    private $article;

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
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return NotesArticle
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
     * @return NotesArticle
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

    /**
     * Set article
     *
     * @param Cron\CronBundle\Entity\Article $article
     * @return NotesArticle
     */
    public function setArticle(\Cron\CronBundle\Entity\Article $article)
    {
        $this->article = $article;
    
        return $this;
    }

    /**
     * Get article
     *
     * @return Cron\CronBundle\Entity\Article 
     */
    public function getArticle()
    {
        return $this->article;
    }
}