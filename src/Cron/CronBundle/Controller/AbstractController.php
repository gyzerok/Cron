<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserLink;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\AdminSettings;
use Cron\CronBundle\Entity\Notepad;

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

        $request->getSession()->set('_srvmsg', $this->getSrvmsg());

        $request->getSession()->set('_notepad', $this->getNotepad());

        $request->getSession()->set('_user_links', $this->getUserLinks());

        $request->getSession()->set('_sound_settings', $this->getSoundSettings());

        $request->getSession()->set('_dialog_list', $this->getDialogList());

        $request->getSession()->set('_invite_list', $this->getInviteList());
    }

    public function getSrvMsg()
    {
        $admin_settings = $this->getDoctrine()->getRepository('CronCronBundle:AdminSettings')->find(1);

        return $admin_settings->getSrvmsg();
    }

    public function getNotepad()
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return "";
        } else {
            $notepad = $this->getDoctrine()->getRepository("CronCronBundle:Notepad")->findOneBy(array("user"=>$user->getId()));

            if (!$notepad instanceof \Cron\CronBundle\Entity\Notepad){
                $notepad = new \Cron\CronBundle\Entity\Notepad();
                $notepad->setUser($user);
                $notepad->setText('');
            }
            $notepad->setDatetime(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($notepad);
            $em->flush();
            return $notepad->getText();
        }
    }

    public function getUserLinks()
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return "";
        } else {
            $links = $this->getDoctrine()->getRepository("CronCronBundle:UserLink")->findBy(array('user' => $user->getId()));

            $html = '';
            foreach ($links as $link) {
                $html .= '<li><a href="'.$link->getUrl().'" target="_blank">'.$link->getTitle().'</a></li>';
            }

            return $html;
        }
    }

    public function getSoundSettings()
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return "sound_inChat sound_personalMessage sound_chatInvite sound_newQuestion sound_questionIsClosed";
        } else {
            $my_settings = $user->getSettings();
            if ($my_settings instanceof UserSettings){
                $classes = "";
                $sounds = $my_settings->getSounds();
                if ($my_settings->getSounds()){
                    foreach ($sounds as $sound=>$val) {
                        if ($val){
                            switch($sound){
                                case 'cats':
                                    break;
                                case 'rush':
                                    break;
                                case 'invite':
                                    $classes .= "sound_chatInvite ";
                                    break;
                                case 'chat':
                                    $classes .= "sound_inChat ";
                                    break;
                                case 'dialog':
                                    $classes .= "sound_personalMessage ";
                                    break;
                                default:break;
                            }
                        }
                    }
//                    $classes = substr($classes,0,strlen($classes)-1);
                }
                return $classes;
            } else {
                return "sound_inChat sound_personalMessage sound_chatInvite sound_newQuestion sound_questionIsClosed";
            }

        }
    }

    public function getDialogList(){
        $user = $this->getUser();
        if ($user instanceof User){
            $dialogs = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')
                ->createQueryBuilder('dialog')
                ->where('(dialog.user1 = :uid AND dialog.del1 = 0 AND dialog.spam1 = 0) OR (dialog.user2 = :uid AND dialog.del2 = 0 AND dialog.spam2 = 0)')
                ->setParameter('uid', $user->getId())
                ->orderBy('dialog.start_date', 'DESC')
                ->getQuery()
                ->getResult();

            $total_unreads = 0;

            foreach ($dialogs as $dialog) {
                $unreads = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')
                    ->createQueryBuilder('dm')
                    ->select('COUNT(dm.dialog) as unreads')
                    ->where('dm.dialog = :did AND dm.read_flag = 0 AND dm.user = :uid')
                    ->setParameter('did', $dialog->getId())
                    ->setParameter('uid', ($dialog->getUser1()==$user ? $dialog->getUser2()->getId():$dialog->getUser1()->getId()))
                    ->groupBy('dm.dialog')
                    ->getQuery()
                    ->getResult();
                $dialog->unreads = '';
                if ($unreads){
                    if ($unreads[0]){
                        $dialog->unreads = '('.$unreads[0]['unreads'].')';
                        $total_unreads += $unreads[0]['unreads'];
                    }
                }
            }

            return $this->renderView("CronCronBundle:Chat:dialogs.html.twig", array(
                    "dialogs" => $dialogs,
                    "total_unreads" => $total_unreads,
                    "curUser" => $user
                )
            );
        } else {
            return '<div class="dialogs-empty-text">Диалогов нет.</div>';
        }
    }

    public function getInviteList(){
        $user = $this->getUser();
        if ($user instanceof User){
            $invites = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')
                ->createQueryBuilder('chat_invite')
                ->where('chat_invite.user2 = :uid')
                ->setParameter('uid', $user->getId())
                ->orderBy('chat_invite.invite_date', 'DESC')
                ->getQuery()
                ->getResult();

            if (count($invites)){
                return $this->renderView("CronCronBundle:Chat:invites.html.twig", array(
                        "invites" => $invites
                    )
                );
            }
        }
        return '<div class="invites-empty-text">Приглашений нет.</div>';
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