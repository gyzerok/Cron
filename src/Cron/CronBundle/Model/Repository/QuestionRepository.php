<?php

namespace Cron\CronBundle\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

define('ACTIVE', 0);
define('HIDDEN', 1);
define('CLOSED', 2);
define('DELETED', 3);

class QuestionRepository extends EntityRepository
{
    public function findAllByUser(\Cron\CronBundle\Entity\User $user)
    {
        $questions = $this->createQueryBuilder('question')
                          ->where('question.user = :uid AND question.status <> :status AND question.hide_on_index = 0 ')
                          ->setParameters(array('status' => DELETED, 'uid' => $user->getId()))
                          ->orderBy('question.datetime', 'DESC')
                          ->getQuery()
                          ->getResult();

        return $questions;
    }

    public function findAllMyByUser(\Cron\CronBundle\Entity\User $user)
    {
        $questions = $this->createQueryBuilder('question')
            ->where('question.user = :uid AND question.status <> :status AND question.hide_on_my_page = 0 ')
            ->setParameters(array('status' => DELETED, 'uid' => $user->getId()))
            ->orderBy('question.datetime', 'DESC')
            ->getQuery()
            ->getResult();

        return $questions;
    }

    public function findAllNotClosedByUser(\Cron\CronBundle\Entity\User $user, $quest_ip = '')
    {
        if (!$quest_ip){
            $questions = $this->createQueryBuilder('question')
                ->where('question.user = :uid AND question.status <> :status AND question.hide_on_index = 0 ')
                ->setParameters(array('status' => CLOSED, 'uid' => $user->getId()))
                ->orderBy('question.datetime', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $questions = $this->createQueryBuilder('question')
                ->where('question.user = :uid AND question.status <> :status AND question.hide_on_index = 0 AND question.user_ip = :user_ip')
                ->setParameters(array('status' => CLOSED, 'uid' => $user->getId(), 'user_ip' => $quest_ip))
                ->orderBy('question.datetime', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $questions;
    }

    public function findAllIncomingByUser(\Cron\CronBundle\Entity\User $user, $type = 'cat')
    {
        switch($type)
        {
            case 'cat':
                $questions = $this->createQueryBuilder('question')
                                  ->innerJoin('question.spams', 'user')
                                  ->where('question.category <> :cid  AND question.status <> :status AND question.spams <> :user')
                                  ->setParameters(array('cid' => '1', 'status' => DELETED, 'user' => $user->getId()))
                                  ->orderBy('question.datetime', 'DESC')
                                  ->getQuery()
                                  ->getResult();
                break;
            case 'rush':
                $questions = $this->createQueryBuilder('question')
                                  ->where('question.category = :cid  AND question.status <> :status AND question')
                                  ->setParameters(array('status' => DELETED, 'cid' => '1'))
                                  ->orderBy('question.datetime', 'DESC')
                                  ->getQuery()
                                  ->getResult();
                break;
            default:
                break;
        }

        return $questions;
    }
}
