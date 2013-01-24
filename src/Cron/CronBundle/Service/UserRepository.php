<?php
namespace Cron\CronBundle\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
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
        return $class === 'Cron\CronBundle\Entity\User';
    }
}
