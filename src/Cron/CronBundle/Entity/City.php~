<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cron\CronBundle\Entity\City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity
 */
class City
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
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    private $state;



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
     * @return City
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
     * Set state
     *
     * @param Cron\CronBundle\Entity\State $state
     * @return City
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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set name_ru
     *
     * @param string $nameRu
     * @return City
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
     * @return City
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
}