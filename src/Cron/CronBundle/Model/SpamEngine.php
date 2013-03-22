<?php

namespace Cron\CronBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;

class SpamEngine
{
    private $doctrine;

    public function __construct(Registry $registry)
    {
        $this->doctrine = $registry;
    }

    public function markQuestionAsSpam(\Cron\CronBundle\Entity\User $user, $qid)
    {
        $question = new Question();
        $question = $this->doctrine->getRepository('CronCronBundle:Question')->findOneById($qid);
        if (!$question instanceof Question)
            throw new \Exception('Question not found');

        if ($question->getSpams()->contains($user))
            throw new \Exception('You have already marked this question as spam');

        $question->addSpam($user);

        if ($question->getBoundary() >= 50)
            $spamBoundary = 10;
        else
            $spamBoundary = 5;

        if ($question->getSpams()->count() >= $spamBoundary)
        {
            $question->setIsSpam(true);

            $user->setSpamActivity($user->getSpamActivity() + 1);

            $this->banUser($user);

            $this->doctrine->getManager()->persist($user);
        }

        $this->doctrine->getManager()->persist($question);
        $this->doctrine->getManager()->flush();
    }

    public function markAnswerAsSpam(\Cron\CronBundle\Entity\User $user, $aid)
    {
        $answer = new Answer();
        $answer = $this->doctrine->getRepository('CronCronBundle:Answer')->findOneById($aid);
        if (!$answer instanceof Answer)
            throw new \Exception('Answer not found');

        if ($answer->getSpams()->contains($user))
            throw new \Exception('You have already marked this answer as spam');

        $answer->addSpam($user);

        if ($answer->getSpams()->count() >= 5)
        {
            $answer->setIsSpam(true);

            $user->setSpamActivity($user->getSpamActivity() + 1);

            $this->banUser($user);

            $this->doctrine->getManager()->persist($user);
        }

        $this->doctrine->getManager()->persist($answer);
        $this->doctrine->getManager()->flush();
    }

    private function banUser(User $user)
    {
        $banDate = new \DateTime();

        if ($user->getLockedTill() < new \DateTime())
            switch($user->getSpamActivity())
            {
                case 5:
                    $banDate->add(new \DateInterval('PT30M'));
                    $user->setLockedTill($banDate);
                    break;
                case 6:
                    $banDate->add(new \DateInterval('P30D'));
                    $user->setLockedTill($banDate);
                    break;
                default:
                    break;
            }
    }
}
