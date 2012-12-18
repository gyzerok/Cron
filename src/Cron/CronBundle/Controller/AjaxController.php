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

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT q, COUNT(a.id) AS answersCount FROM CronCronBundle:Question q INNER JOIN q.id a WHERE q.pub_date > :pubDate ORDER BY p.pub_date DESC')->setParameter('pubDate', $lastTime);

            $questions = $query->getResult();

            //TODO Разработать формат xml
            $xml = '';
            foreach($questions as $question)
            {
                //TODO Добавить подсчет числа комментариев
                //$count = $em->createQuery(
                    //'SELECT COUNT(*) FROM CronCronBundle:Answer a WHERE a.answer_id = :id')->setParameter('id', $question->getAnswer()
                //);
                //$xml = $xml . sprintf('<question text="%s" date="%d" answers="%d" >', $question->getText, $question->getDatetime, $count);
            }

            return new Response($xml);
        }

        return new Response('error');
    }
}
