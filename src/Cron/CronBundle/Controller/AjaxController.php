<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    public function getStatesAction(Request $request)
    {
        if (!empty($request) && $request->getMethod() == 'POST')
        {
            $countryId = $request->get("country_id", null, false);

            $states = $this->getDoctrine()->getRepository('CronCronBundle:State')->findByCountry($countryId);

            $html = '';
            foreach($states as $state)
            {
                $html = $html . sprintf("<option value=\"%d\">%s</option>", $state->getId(), $state->getName());
            }

            return new Response($html);
        }
    }

    public function getCitiesAction(Request $request)
    {
        if (!empty($request) && $request->getMethod() == 'POST')
        {
            $stateId = $request->get("state_id", null, false);

            $cities = $this->getDoctrine()->getRepository('CronCronBundle:City')->findByState($stateId);

            $html = '';
            foreach($cities as $city)
            {
                $html = $html . sprintf("<option value=\"%d\">%s</option>", $city->getId(), $city->getName());
            }

            return new Response($html);
        }
    }
}
