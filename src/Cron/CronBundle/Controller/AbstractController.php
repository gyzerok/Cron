<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends Controller implements InitializableControllerInterface
{
    protected $onlineUserCount;
    protected $totalUserCount;
    protected $user;

    public function initialize(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $this->user = $this->getUser();

        $this->updateUserCounters($request);
    }

    private function updateUserCounters(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sid = $request->getSession()->getId();
        $isOnline = $this->getDoctrine()->getRepository('CronCronBundle:Online')->findBySid($sid);
        if (empty($isOnline))
        {
            $onlineEntry = new \Cron\CronBundle\Entity\Online($sid);
            $em->persist($onlineEntry);
        }

        $timeBoundary = new \DateTime();
        $timeBoundary->sub(new \DateInterval('PT10M'));
        $offlines = $this->getDoctrine()->getRepository('CronCronBundle:Online')
                                        ->createQueryBuilder('online')
                                        ->where('online.lastVisit < :lastVisit')
                                        ->setParameter('lastVisit', $timeBoundary)
                                        ->getQuery()->getResult();

        foreach ($offlines as $offline)
            $em->remove($offline);
        $em->flush();

        $onlineUserCount = $this->getDoctrine()->getRepository('CronCronBundle:Online')
                                               ->createQueryBuilder('online')
                                               ->select('COUNT(online.sid) AS onlineCount')
                                               ->getQuery()->getResult();

        $totalUserCount = $this->getDoctrine()->getRepository('CronCronBundle:User')
                                               ->createQueryBuilder('user')
                                               ->select('COUNT(user.id) AS totalCount')
                                               ->getQuery()->getResult();

        $this->onlineUserCount = $onlineUserCount[0]['onlineCount'];
        $this->totalUserCount = $totalUserCount[0]['totalCount'];
    }
}
