<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\AdminSettings
 *
 * @ORM\Table(name="admin_settings")
 * @ORM\Entity
 */
class AdminSettings {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $srvmsg
     *
     * @ORM\Column(name="srvmsg", type="text", nullable=true)
     */
    protected $srvmsg;

    /**
     * @var integer $credit_currency
     *
     * @ORM\Column(name="credit_currency", type="integer", nullable=false)
     */
    protected $credit_currency;

    /**
     * @var integer $credit_currency
     *
     * @ORM\Column(name="answers50", type="integer", nullable=false)
     */
    protected $answers50;

    /**
     * @var integer $credit_currency
     *
     * @ORM\Column(name="answers100", type="integer", nullable=false)
     */
    protected $answers100;

    /**
     * @var integer $credit_currency
     *
     * @ORM\Column(name="answers1000", type="integer", nullable=false)
     */
    protected $answers1000;


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
     * Set srvmsg
     *
     * @param string $srvmsg
     * @return AdminSettings
     */
    public function setSrvmsg($srvmsg)
    {
        $this->srvmsg = $srvmsg;
    
        return $this;
    }

    /**
     * Get srvmsg
     *
     * @return string 
     */
    public function getSrvmsg()
    {
        return $this->srvmsg;
    }

    /**
     * Set credit_currency
     *
     * @param integer $creditCurrency
     * @return AdminSettings
     */
    public function setCreditCurrency($creditCurrency)
    {
        $this->credit_currency = $creditCurrency;
    
        return $this;
    }

    /**
     * Get credit_currency
     *
     * @return integer 
     */
    public function getCreditCurrency()
    {
        return $this->credit_currency;
    }

    /**
     * Set answers50
     *
     * @param integer $answers50
     * @return AdminSettings
     */
    public function setAnswers50($answers50)
    {
        $this->answers50 = $answers50;
    
        return $this;
    }

    /**
     * Get answers50
     *
     * @return integer 
     */
    public function getAnswers50()
    {
        return $this->answers50;
    }

    /**
     * Set answers100
     *
     * @param integer $answers100
     * @return AdminSettings
     */
    public function setAnswers100($answers100)
    {
        $this->answers100 = $answers100;
    
        return $this;
    }

    /**
     * Get answers100
     *
     * @return integer 
     */
    public function getAnswers100()
    {
        return $this->answers100;
    }

    /**
     * Set answers1000
     *
     * @param integer $answers1000
     * @return AdminSettings
     */
    public function setAnswers1000($answers1000)
    {
        $this->answers1000 = $answers1000;
    
        return $this;
    }

    /**
     * Get answers1000
     *
     * @return integer 
     */
    public function getAnswers1000()
    {
        return $this->answers1000;
    }
}