<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            {
                $html = $html . sprintf('<option value="%d">%s</option>', $state->getId(), $state->getName());
            }

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
            {
                $html = $html . sprintf('<option value="%d">%s</option>', $city->getId(), $city->getName());
            }

            return new Response($html);
        }
    }

    public function getUpdateAction(Request $request)
    {
        if($request->isMethod('POST'))
        {
            $lastTime = $request->get("last_time");

            $questionRepo = $this->getDoctrine()->getRepository('CronCronBundle:Question');
            $catQuery = $questionRepo->createQueryBuilder('question')
                                     ->innerJoin('question.user', 'user')
                                     ->where('question.category > :cid AND question.datetime > :lastTime')
                                     ->setParameter('cid', '1')
                                     ->setParameter('lastTime',$lastTime)
                                     ->getQuery();
            $categorized = $catQuery->getResult();

            $rushQuery = $questionRepo->createQueryBuilder('question')
                                      ->innerJoin('question.user', 'user')
                                      ->where('question.category = :cid AND question.datetime > :lastTime')
                                      ->setParameter('cid', '1')
                                      ->setParameter('lastTime',$lastTime)
                                      ->getQuery();
            $rush = $rushQuery->getResult();

            $json = '{';

            $json = $json . '"categorized":{' . $this->questionsToJSON($categorized) . '},';
            $json = $json . '"rush:{"' . $this->questionsToJSON($rush) . '},';
            $json = $json . '"last_time":' . time();
            $json = $json . '}';

            return new Response($json);
        }

        return new Response('error');
    }

    public function questionsToJSON(array $questions)
    {
        $json = '';
        foreach($questions as $question)
            $json = $json . sprintf('"%d":{ "text":"%s", "user":"%s", "date":"%s"}', $question->getId(), $question->getText(), $question->getUser(), $question->getDatetime()->format('H:i:s d.m.Y'));

        return $json;
    }
}
