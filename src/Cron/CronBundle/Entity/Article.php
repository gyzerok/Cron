<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Cron\CronBundle\Entity\ArticleCategory;

/**
 * Cron\CronBundle\Entity\Article
 *
 * @ORM\Table(name="article")
 * @ORM\Entity
 */
class Article {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $header
     *
     * @ORM\Column(name="header", type="string", length=512, nullable=false)
     */
    private $header;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    private $locale;

    /**
     * @var ArticleCategory
     *
     * @ORM\ManyToOne(targetEntity="ArticleCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category", referencedColumnName="id", nullable=false)
     * })
     */
    private $category;

    /**
     * @var string $imgs
     *
     * @ORM\Column(name="imgs", type="string", length=512, nullable=true)
     */
    private $imgs;

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var integer $link_type
     *
     * @ORM\Column(name="link_type", type="integer", nullable=false)
     */
    private $link_type;

    /**
     * @var string $link_value
     *
     * @ORM\Column(name="link_value", type="string", length=512, nullable=false)
     */
    private $link_value;

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
     * Set header
     *
     * @param string $header
     * @return Article
     */
    public function setHeader($header)
    {
        $this->header = $header;
    
        return $this;
    }

    /**
     * Get header
     *
     * @return string 
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return Article
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    
        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set imgs
     *
     * @param string $imgs
     * @return Article
     */
    public function setImgs($imgs)
    {
        $this->imgs = serialize($imgs);
    
        return $this;
    }

    /**
     * Get imgs
     *
     * @return string 
     */
    public function getImgs()
    {
        return unserialize($this->imgs);
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Article
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
     * Set link_type
     *
     * @param integer $linkType
     * @return Article
     */
    public function setLinkType($linkType)
    {
        $this->link_type = $linkType;
    
        return $this;
    }

    /**
     * Get link_type
     *
     * @return integer 
     */
    public function getLinkType()
    {
        return $this->link_type;
    }

    public function getLinkTypeTextly()
    {
        switch($this->link_type){
            case "1":
                $link_type_textly = "Источник";
                break;
            case "2":
                $link_type_textly = "Ссылка";
                break;
            case "3":
                $link_type_textly = "Автор статьи";
                break;
            default:
                $link_type_textly = "";
                break;
        }
        return $link_type_textly;
    }

    /**
     * Set link_value
     *
     * @param string $linkValue
     * @return Article
     */
    public function setLinkValue($linkValue)
    {
        $this->link_value = $linkValue;
    
        return $this;
    }

    /**
     * Get link_value
     *
     * @return string 
     */
    public function getLinkValue()
    {
        return $this->link_value;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Article
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
     * Set category
     *
     * @param Cron\CronBundle\Entity\ArticleCategory $category
     * @return Article
     */
    public function setCategory(ArticleCategory $category)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return Cron\CronBundle\Entity\ArticleCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }
}