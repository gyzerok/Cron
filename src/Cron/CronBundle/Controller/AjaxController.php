<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cron\CronBundle\Entity\Answer;

class AjaxController extends Controller
{
    public function getStatesAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $countryId = $request->get("country_id");

            $states = $this->getDoctrine()->getRepository('CronCronBundle:State')->findByCountry($countryId);

            $html = '';
            foreach($states as $state)
                $html = $html . sprintf('<option value="%d">%s</option>', $state->getId(), $state->getName());

            return new Response($html);
        }
    }

    public function getCitiesAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $stateId = $request->get("state_id");

            $cities = $this->getDoctrine()->getRepository('CronCronBundle:City')->findByState($stateId);

            $html = '';
            foreach($cities as $city)
                $html = $html . sprintf('<option value="%d">%s</option>', $city->getId(), $city->getName());

            return new Response($html);
        }
    }

    public function getUpdateAction(Request $request)
    {
        if($request->isMethod('POST'))
        {
            $lastTime = $request->get("last_time");
            $lastTime = date('Y-m-d H:i:s', $lastTime);

            $questionRepo = $this->getDoctrine()->getRepository('CronCronBundle:Question');
            $catQuery = $questionRepo->createQueryBuilder('question')
                                     ->innerJoin('question.user', 'user')
                                     ->where('question.category > :cid AND question.datetime > :lastTime AND question.status <> :status')
                                     ->setParameter('cid', '1')
                                     ->setParameter('lastTime', $lastTime)
                                     ->setParameter('status', '2')
                                     ->getQuery();
            $categorized = $catQuery->getResult();

            $rushQuery = $questionRepo->createQueryBuilder('question')
                                      ->innerJoin('question.user', 'user')
                                      ->where('question.category = :cid AND question.datetime > :lastTime AND question.status <> :status')
                                      ->setParameter('cid', '1')
                                      ->setParameter('lastTime', $lastTime)
                                      ->setParameter('status', '2')
                                      ->getQuery();
            $rush = $rushQuery->getResult();
            $answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')
                                           ->createQueryBuilder('answer')
                                           ->innerJoin('answer.user', 'user')
                                           ->where('answer.status <> :status')
                                           ->setParameter('status', '0')
                                           ->getQuery()->getRuslt();

            $json = '{';

            $json = $json . '"categorized":{' . $this->questionsToJSON($categorized) . '},';
            $json = $json . '"rush":{' . $this->questionsToJSON($rush) . '},';
            //$json = $json . '"answers":{' . $this->questionsToJSON()
            $json = $json . '"last_time":' . time();
            $json = $json . '}';

            return new Response($json);
        }

        return new Response('error');
    }

    public function delQuestionAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $questionId = $request->get("question_id");
            $full = $request->get("full");

            $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
            if (!$question instanceof \Cron\CronBundle\Entity\Question)
                return new Response('Fail');

            if ($full)
                $question->setStatus(2);
            else
                $question->setStatus(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return new Response('SUCCESS');
        }

        return new Response('Fail');
    }

    public function likeItemAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($questionId = $request->get("question_id"))
            {
                $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
                if (!$question instanceof \Cron\CronBundle\Entity\Question)
                    return new Response('Fail');

                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');
                    //$user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');

                $question->addLikes($user);

                return new Response('Succsess');
            }
        }

        return new Response('Fail');
    }

    public function postAnswerAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $questionId = $request->get("id");
            $answerText = $request->get("text");

            $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
            if (!$question instanceof \Cron\CronBundle\Entity\Question)
                return new Response('Fail');

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setText($answerText);
            $user = $this->getUser();
            if (!$user instanceof User)
                $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
            $answer->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            return new Response('SUCCESS');
        }
    }

    public function questionsToJSON(array $questions)
    {
        $json = '';
        foreach($questions as $question)
            $json = $json . sprintf('"%d":{ "text":"%s", "user":"%s", "date":"%s"},', $question->getId(), $question->getText(), $question->getUser(), $question->getDatetime()->format('H:i:s d.m.Y'));

        $json = substr($json, 0, -1);
        return $json;
    }
}
