<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\UserSettings
 *
 * @ORM\Table(name="user_settings")
 * @ORM\Entity
 */
class UserSettings {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user;

    /**
     * @var string $income_cats
     *
     * @ORM\Column(name="income_cats", type="string", length=256, nullable=true)
     */
    protected $income_cats;

    /**
     * @var string $income_locale
     *
     * @ORM\Column(name="income_locale", type="string", length=128, nullable=true)
     */
    protected $income_locale;

    /**
     * @var string $view_cats
     *
     * @ORM\Column(name="view_cats", type="string", length=256, nullable=true)
     */
    protected $view_cats;

    /**
     * @var string $view_locale
     *
     * @ORM\Column(name="view_locale", type="string", length=128, nullable=true)
     */
    protected $view_locale;

    /**
     * @var string $view_by_time
     *
     * @ORM\Column(name="view_by_time", type="string", length=16, nullable=true)
     */
    protected $view_by_time;

    /**
     * @var string $sounds
     *
     * @ORM\Column(name="sounds", type="string", length=128, nullable=true)
     */
    protected $sounds;




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
     * Set income_cats
     *
     * @param string $incomeCats
     * @return UserSettings
     */
    public function setIncomeCats($incomeCats)
    {
        $this->income_cats = serialize($incomeCats);
    
        return $this;
    }

    /**
     * Get income_cats
     *
     * @return Array
     */
    public function getIncomeCats()
    {
        return unserialize($this->income_cats);
    }

    /**
     * Set income_locale
     *
     * @param string $incomeLocale
     * @return UserSettings
     */
    public function setIncomeLocale($incomeLocale)
    {
        $this->income_locale = serialize($incomeLocale);
    
        return $this;
    }

    /**
     * Get income_locale
     *
     * @return Array
     */
    public function getIncomeLocale()
    {
        return unserialize($this->income_locale);
    }

    /**
     * Set view_cats
     *
     * @param string $viewCats
     * @return UserSettings
     */
    public function setViewCats($viewCats)
    {
        $this->view_cats = serialize($viewCats);
    
        return $this;
    }

    /**
     * Get view_cats
     *
     * @return Array
     */
    public function getViewCats()
    {
        return unserialize($this->view_cats);
    }

    /**
     * Set view_locale
     *
     * @param string $viewLocale
     * @return UserSettings
     */
    public function setViewLocale($viewLocale)
    {
        $this->view_locale = serialize($viewLocale);
    
        return $this;
    }

    /**
     * Get view_locale
     *
     * @return Array
     */
    public function getViewLocale()
    {
        return unserialize($this->view_locale);
    }

    /**
     * Set view_by_time
     *
     * @param string $viewByTime
     * @return UserSettings
     */
    public function setViewByTime($viewByTime)
    {
        $this->view_by_time = $viewByTime;
    
        return $this;
    }

    /**
     * Get view_by_time
     *
     * @return string 
     */
    public function getViewByTime()
    {
        return $this->view_by_time;
    }

    /**
     * Set sounds
     *
     * @param string $sounds
     * @return UserSettings
     */
    public function setSounds($sounds)
    {
        $this->sounds = serialize($sounds);
    
        return $this;
    }

    /**
     * Get sounds
     *
     * @return Array
     */
    public function getSounds()
    {
        return unserialize($this->sounds);
    }

    /**
     * Set user
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return UserSettings
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