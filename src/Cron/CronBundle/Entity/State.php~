<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cron\CronBundle\Entity\State
 *
 * @ORM\Table(name="state")
 * @ORM\Entity
 */
class State
{
    private $locale = 'ru_RU';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name_ru
     *
     * @ORM\Column(name="name_ru", type="string", length=128, nullable=false)
     */
    private $name_ru;

    /**
     * @var string $name_en
     *
     * @ORM\Column(name="name_en", type="string", length=128, nullable=false)
     */
    private $name_en;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return State
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName($locale = 'ru_RU')
    {
        $this->locale = $locale;

        switch($this->locale){
            case 'ru_RU':
                $name = $this->getNameRu();
                break;
            case 'en_US':
            case 'pt_PT':
            default:
                $name = $this->getNameEn();
                break;
        }
        return $name;
    }

    /**
     * Set country
     *
     * @param Cron\CronBundle\Entity\Country $country
     * @return State
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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set name_ru
     *
     * @param string $nameRu
     * @return State
     */
    public function setNameRu($nameRu)
    {
        $this->name_ru = $nameRu;
    
        return $this;
    }

    /**
     * Get name_ru
     *
     * @return string 
     */
    public function getNameRu()
    {
        return $this->name_ru;
    }

    /**
     * Set name_en
     *
     * @param string $nameEn
     * @return State
     */
    public function setNameEn($nameEn)
    {
        $this->name_en = $nameEn;
    
        return $this;
    }

    /**
     * Get name_en
     *
     * @return string 
     */
    public function getNameEn()
    {
        return $this->name_en;
    }

    public function __construct($session)
    {
        $this->locale = $session->getLocale();

        return $this;
    }
}