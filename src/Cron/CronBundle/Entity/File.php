<?php

namespace Cron\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cron\CronBundle\Entity\File
 *
 * @ORM\Table(name="file")
 * @ORM\Entity
 */
class File {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $text
     *
     * @ORM\Column(name="filename", type="string", length=256, nullable=false)
     */
    protected $filename;

    /**
     * @var string $text
     *
     * @ORM\Column(name="url", type="string", length=512, nullable=false)
     */
    protected $url;

    /**
     * @var string $text
     *
     * @ORM\Column(name="hash", type="string", length=100, nullable=false)
     */
    protected $hash;

    /**
     * @var \DateTime $datetime
     *
     * @ORM\Column(name="upload_date", type="datetime", nullable=false)
     */
    protected $upload_date;

    /**
     * @var integer $filesize
     *
     * @ORM\Column(name="filesize", type="integer")
     */
    protected $filesize;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    protected $user;

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
     * Set filename
     *
     * @param string $filename
     * @return File
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return File
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set upload_date
     *
     * @param \DateTime $uploadDate
     * @return File
     */
    public function setUploadDate($uploadDate)
    {
        $this->upload_date = $uploadDate;
    
        return $this;
    }

    /**
     * Get upload_date
     *
     * @return \DateTime 
     */
    public function getUploadDate()
    {
        return $this->upload_date;
    }

    /**
     * Set user_id
     *
     * @param Cron\CronBundle\Entity\User $user
     * @return File
     */
    public function setUser(\Cron\CronBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return Cron\CronBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return File
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    
        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     * @return File
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
    
        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer 
     */
    public function getFilesize()
    {
        $filesize = $this->filesize;
        if($filesize > 1024){
            $filesize = ($filesize/1024);
            if($filesize > 1024){
                $filesize = ($filesize/1024);
                if($filesize > 1024){
                    $filesize = ($filesize/1024);
                    $filesize = round($filesize, 1);
                    return $filesize." ГБ";
                } else {
                    $filesize = round($filesize, 1);
                    return $filesize." MБ";
                }
            } else {
                $filesize = round($filesize, 1);
                return $filesize." Кб";
            }
        } else {
            $filesize = round($filesize, 1);
            return $filesize." байт";
        }
//        return $this->filesize;
    }
}