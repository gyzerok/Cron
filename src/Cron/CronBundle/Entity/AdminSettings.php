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
     * @var string $option
     *
     * @ORM\Column(name="admin_option", type="string", length=256, nullable=true)
     */
    protected $option;

    /**
     * @var string $value
     *
     * @ORM\Column(name="admin_value", type="string", length=256, nullable=true)
     */
    protected $value;



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
     * Set option
     *
     * @param string $option
     * @return AdminSettings
     */
    public function setOption($option)
    {
        $this->option = $option;
    
        return $this;
    }

    /**
     * Get option
     *
     * @return string 
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return AdminSettings
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }
}