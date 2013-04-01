<?php

namespace Cron\CronBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Bundle\DoctrineBundle\Registry;
//use Doctrine\ORM\EntityManager;
//use Doctrine\ORM\EntityRepository;

use Symfony\Component\Validator\Constraints as Assert;


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
     *
     * @Assert\Length(
     *      min = "2",
     *      max = "12",
     *      minMessage = "некорректное заполнение|некорректное заполнение",
     *      maxMessage = "некорректное заполнение|некорректное заполнение"
     * )
     * @Assert\Regex(
     *      pattern = "/\d/",
     *      match = false,
     *      message = "некорректное заполнение"
     * )
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
     * @var \DateTime $lockedTill
     *
     * @ORM\Column(name="locked_till", type="datetime", nullable=false)
     */
    private $lockedTill;

    /**
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", nullable=true, referencedColumnName="id")
     * })
     */
    private $state = null;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $city = null;

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
     * @var integer $role
     *
     * @ORM\Column(name="role", type="integer", nullable=false)
     */
    private $role = 0;

    /**
     * @var integer $spamActivity
     *
     * @ORM\Column(name="spam_activity", type="integer", nullable=false)
     */
    private $spamActivity;

    /**
     * @var integer $credits
     *
     * @ORM\Column(name="credits", type="integer", nullable=false)
     */
    private $credits = 0;

    /**
     * @ORM\OneToOne(targetEntity="UserSettings")
     * @ORM\JoinColumn(name="settings", referencedColumnName="id")
     */
    private $settings;


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

    public function getGenderLetter()
    {
        $letter = "";
        switch($this->gender){
            case "1":
                $letter = "М";
                break;
            case "2":
                $letter = "Ж";
                break;
            default:break;
        }
        return $letter;
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

    public function getAge()
    {
        return $this->birthDate->diff(new \DateTime())->y;
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
        $role = "";
        switch($this->getRole()){
            case '2':
                $role = 'ROLE_ADMIN';
                break;
            case '1':
                $role = 'ROLE_MODERATOR';
                break;
            case '0':
            default:
                $role = 'ROLE_USER';
                break;
        }
        return array($role);
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
        return serialize(array($this->id, $this->username, $this->nick, $this->lockedTill));
    }

    public function unserialize($data)
    {
        list(
            $this->id,
            $this->username,
            $this->nick,
            $this->lockedTill
            ) = unserialize($data);
    }

    public function __construct()
    {
        $this->regDate = new \DateTime();
        $this->lastVisit = new \DateTime();
        $this->isActive = false;
        $this->spamActivity = 0;
    }

    public function __toString()
    {
        return $this->nick;
    }

    function equals(UserInterface $user)
    {
        if ($this->username != $user->getUsername() || $this->password != $user->getPassword() || $this->lockedTill != $user->getLockedTill())
            return false;

        return true;
    }

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

    /**
     * Set role
     *
     * @param integer $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return integer 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set lockedTill
     *
     * @param \DateTime $lockedTill
     * @return User
     */
    public function setLockedTill($lockedTill)
    {
        $this->lockedTill = $lockedTill;
    
        return $this;
    }

    /**
     * Get lockedTill
     *
     * @return \DateTime 
     */
    public function getLockedTill()
    {
        return $this->lockedTill;
    }

    /**
     * Set spamActivity
     *
     * @param integer $spamActivity
     * @return User
     */
    public function setSpamActivity($spamActivity)
    {
        $this->spamActivity = $spamActivity;
    
        return $this;
    }

    /**
     * Get spamActivity
     *
     * @return integer 
     */
    public function getSpamActivity()
    {
        return $this->spamActivity;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     * @return User
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;
    
        return $this;
    }

    /**
     * Get credits
     *
     * @return integer 
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /*
     * Добавление 1 кредита после лайка ответа, созданного пользователем
     */
    public function incCredits()
    {
        $this->credits++;

        return $this;
    }


    /**
     * Set settings
     *
     * @param Cron\CronBundle\Entity\UserSettings $settings
     * @return User
     */
    public function setSettings(\Cron\CronBundle\Entity\UserSettings $settings = null)
    {
        $this->settings = $settings;
    
        return $this;
    }

    /**
     * Get settings
     *
     * @return Cron\CronBundle\Entity\UserSettings 
     */
    public function getSettings()
    {
        return $this->settings;
    }
}