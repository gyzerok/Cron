<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Cron\CronBundle\Entity\Country
 *
 * @ORM\Table(name="country")
 * @ORM\Entity
 */
class Country
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
     * @return Country
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
    public function getName()
    {
        $name = $this->getNameRu();
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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set name_ru
     *
     * @param string $nameRu
     * @return Country
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
     * @return Country
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