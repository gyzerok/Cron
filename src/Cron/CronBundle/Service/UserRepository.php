<?php
namespace Cron\CronBundle\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Cron\CronBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
//use Symfony\Component\Security\Core\User\User;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        //$repository = $this->getDoctrine()->getRepository('CronCronBundle:User');
        $q = $this->createQueryBuilder('u')
                  ->where('u.isActive = 1 AND u.username = :username')
                  ->setParameter('username', $username)
                  ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(sprintf('Unable to find an active User object identified by "%s".', $username), null, 0, $e);
        }

        //$currentTime = new \DateTime();
        if ($user instanceof \Cron\CronBundle\Entity\User)
            if ($user->getLockedTill() > new \DateTime())
                throw new LockedException(sprintf('You are locked till %s', $user->getLockedTill()->format("H:i d.m.Y")));

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \Cron\CronBundle\Entity\User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class == 'Cron\CronBundle\Entity\User';
    }
}