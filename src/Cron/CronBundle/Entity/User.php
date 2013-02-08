<?php

namespace Cron\CronBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Bundle\DoctrineBundle\Registry;
//use Doctrine\ORM\EntityManager;
//use Doctrine\ORM\EntityRepository;


/**
 * Cron\CronBundle\Entity\User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Cron\CronBundle\Service\UserRepository")
 */
class User implements UserInterface, \Serializable
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
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=45, nullable=false)
     */
    private $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=129, nullable=false)
     */
    private $password;

    /**
     * @var string $nick
     *
     * @ORM\Column(name="nick", type="string", length=45, nullable=false)
     */
    private $nick;

    /**
     * @var boolean $gender
     *
     * @ORM\Column(name="gender", type="boolean", nullable=false)
     */
    private $gender;

    /**
     * @var \DateTime $birthDate
     *
     * @ORM\Column(name="birth_date", type="date", nullable=false)
     */
    private $birthDate;

    /**
     * @var \DateTime $regDate
     *
     * @ORM\Column(name="reg_date", type="datetime", nullable=false)
     */
    private $regDate;

    /**
     * @var boolean $agreement
     *
     * @ORM\Column(name="agreement", type="boolean", nullable=false)
     */
    private $agreement;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var \DateTime $lastVisit
     *
     * @ORM\Column(name="last_visit", type="datetime", nullable=false)
     */
    private $lastVisit;

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
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $city;

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set gender
     *
     * @param boolean $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    
        return $this;
    }

    /**
     * Get gender
     *
     * @return boolean 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    
        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime 
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set regDate
     *
     * @param \DateTime $regDate
     * @return User
     */
    public function setRegDate($regDate)
    {
        $this->regDate = $regDate;
    
        return $this;
    }

    /**
     * Get regDate
     *
     * @return \DateTime 
     */
    public function getRegDate()
    {
        return $this->regDate;
    }

    /**
     * Set agreement
     *
     * @param boolean $agreement
     * @return User
     */
    public function setAgreement($agreement)
    {
        $this->agreement = $agreement;
    
        return $this;
    }

    /**
     * Get agreement
     *
     * @return boolean 
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * Set lastVisit
     *
     * @param \DateTime $lastVisit
     * @return User
     */
    public function setLastVisit($lastVisit)
    {
        $this->lastVisit = $lastVisit;
    
        return $this;
    }

    /**
     * Get lastVisit
     *
     * @return \DateTime 
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     * Set state
     *
     * @param Cron\CronBundle\Entity\State $state
     * @return User
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

    /**
     * Set city
     *
     * @param Cron\CronBundle\Entity\City $city
     * @return User
     */
    public function setCity(\Cron\CronBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return Cron\CronBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param Cron\CronBundle\Entity\Country $country
     * @return User
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

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getSalt()
    {
        return 'salt';
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array($this->id, $this->username, $this->nick));
    }

    public function unserialize($data)
    {
        list(
            $this->id,
            $this->username,
            $this->nick
            ) = unserialize($data);
    }

    public function __construct()
    {
        $this->setRegDate(new \DateTime());
        $this->setLastVisit(new \DateTime());
        $this->setIsActive(false);
    }

    public function __toString()
    {
        return $this->getNick();
    }

    function equals(UserInterface $user)
    {
        if ($this->username != $user->getUsername() || $this->password != $user->getPassword())
            return false;

        return true;
    }

    /*public function __sleep(){
        return array('id', 'username', 'email', 'password', 'regDate', 'birthDate', "agreement", "gender", "lastVisit", "city", "state", "country");
    }*/

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set nick
     *
     * @param string $nick
     * @return User
     */
    public function setNick($nick)
    {
        $this->nick = $nick;
    
        return $this;
    }

    /**
     * Get nick
     *
     * @return string 
     */
    public function getNick()
    {
        return $this->nick;
    }
}