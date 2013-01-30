<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cron\CronBundle\Entity\User;

use Cron\CronBundle\Entity\Chat;
use Cron\CronBundle\Entity\ChatInvite;
use Cron\CronBundle\Entity\ChatMember;
use Cron\CronBundle\Entity\ChatMsg;

use Cron\CronBundle\Entity\Dialog;
use Cron\CronBundle\Entity\DialogMsg;

class ChatController extends Controller
{

    public function loadChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $dialogs = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')
                ->createQueryBuilder('dialog')
                ->where('(dialog.user1 = :uid AND dialog.del1 = 0 AND dialog.spam1 = 0 AND dialog.open1 = 1) OR (dialog.user2 = :uid AND dialog.del2 = 0 AND dialog.spam2 = 0 AND dialog.open2 = 1)')
                ->setParameter('uid', $user->getId())
                ->orderBy('dialog.start_date', 'DESC')
                ->getQuery()
                ->getResult();

            return $this->render("CronCronBundle:Chat:chatwindow.html.twig", array(
                    "dialogs" => $dialogs,
                    "curUser" => $user
                )
            );
        }
    }

    public function updateChatAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){
            return new Response('SUCCESS');
        }
    }

    public function getSrvMsgAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){
            return new Response('SUCCESS');
        }
    }

    public function getDialogListAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $dialogs = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')
                ->createQueryBuilder('dialog')
//                ->select('id, user1, user2, start_date, COUNT(dm.id) as unreads')
//                ->leftJoin('Cron\CronBundle\Entity\DialogMsg', 'dm', 'WITH', 'dialog.id = dm.dialog')
//                ->leftJoin('dialog.id', 'dialogmsg')
                ->where('(dialog.user1 = :uid AND dialog.del1 = 0 AND dialog.spam1 = 0) OR (dialog.user2 = :uid AND dialog.del2 = 0 AND dialog.spam2 = 0)')
                ->setParameter('uid', $user->getId())
                ->orderBy('dialog.start_date', 'DESC')
//                ->groupBy('dm.dialog')
                ->getQuery()
                ->getResult();
//            echo '<pre>';
//            print_r($dialogs);
//            echo '</pre>';
            foreach ($dialogs as $dialog) {
                $unreads = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')
                    ->createQueryBuilder('dm')
                    ->select('COUNT(dm.dialog) as unreads')
                    ->where('dm.dialog = :did AND dm.read_flag = 0 AND dm.user = :uid')
                    ->setParameter('did', $dialog->getId())
                    ->setParameter('uid', ($dialog->getUser1()==$user ? $dialog->getUser2()->getId():$dialog->getUser1()->getId()))
//                    ->setParameter('uid', $dialog->getUser2())
                    ->groupBy('dm.dialog')
                    ->getQuery()
                    ->getResult();
                if ($unreads[0])
                    $dialog->unreads = '('.$unreads[0]['unreads'].')';
//                print_r($unreads);
            }

            return $this->render("CronCronBundle:Chat:dialogs.html.twig", array(
                    "dialogs" => $dialogs,
                    "curUser" => $user
                )
            );
        }
    }

    public function getInviteListAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $invites = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')
                ->createQueryBuilder('chat_invite')
                ->where('chat_invite.user2 = :uid')
                ->setParameter('uid', $user->getId())
                ->orderBy('chat_invite.invite_date', 'DESC')
                ->getQuery()
                ->getResult();

            //todo srvmsg

            return $this->render("CronCronBundle:Chat:invites.html.twig", array(
                    "invites" => $invites
                )
            );
        }
    }

    public function getDialogMsgsAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){
            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog instanceof \Cron\CronBundle\Entity\Dialog){
                $messages = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')
                    ->createQueryBuilder('dialog_msg')
                    ->where('dialog_msg.dialog = :did')
                    ->setParameter('did', $dialog->getId())
                    ->orderBy('dialog_msg.msg_date', 'DESC')
                    ->getQuery()
                    ->getResult();
                return new Response(serialize($messages));
            } else return new Response('Fail');
        }
    }

    public function getChatMsgsAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $request->get('chat')));
            if ($chat instanceof \Cron\CronBundle\Entity\Chat){
                $messages = $this->getDoctrine()->getRepository('CronCronBundle:ChatMsg')
                    ->createQueryBuilder('chat_msg')
                    ->where('chat_msg.chat = :cid')
                    ->setParameter('cid', $chat->getId())
                    ->orderBy('chat_msg.msg_date', 'DESC')
                    ->getQuery()
                    ->getResult();
                return new Response(serialize($messages));
            } else return new Response('Fail');
        }
    }

    public function getMyChatAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

            //TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            return new Response('SUCCESS');
        }
    }

    public function getIncomeChatsAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

            //TODO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            return new Response('SUCCESS');
        }
    }

    public function sendDialogMsgAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $em = $this->getDoctrine()->getManager();

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if (!$dialog instanceof \Cron\CronBundle\Entity\Dialog){
                $dialog = new Dialog();
                $dialog->setUser1($user->getId());
                $dialog->setUser2($request->get('to_user'));
                $dialog->setStartDate(new \DateTime());

                $em->persist($dialog);
                $em->flush();
            }
            $dialog_msg = new DialogMsg();
            $dialog_msg->setDialog($dialog);
            $dialog_msg->setUser($user);
            $dialog_msg->setMsgDate(new \DateTime());
            $dialog_msg->setMsgText($request->get('message'));
            $dialog_msg->setReadFlag(0);

            $em->persist($dialog_msg);
            $em->flush();
            return new Response('SUCCESS');
        }
    }

    public function sendChatMsgAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $request->get('chat')));
            if ($chat instanceof \Cron\CronBundle\Entity\Chat){
                $chat_msg = new ChatMsg();
                $chat_msg->setChat($chat);
                $chat_msg->setUser($user);
                $chat_msg->setMsgDate(new \DateTime());
                $chat_msg->setMsgText($request->get('message'));
                $chat_msg->setReadFlag(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($chat_msg);
                $em->flush();
            }
            return new Response('SUCCESS');
        }
    }

    public function deleteDialogAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog->getUser1() == $user){
                $dialog->setDel1(1);
            } elseif ($dialog->getUser2() == $user){
                $dialog->setDel2(1);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    public function checkDialogAsSpamAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog->getUser1() == $user){
                $dialog->setSpam1(1);
            } elseif ($dialog->getUser2() == $user){
                $dialog->setSpam2(1);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    public function acceptChatInviteAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $em = $this->getDoctrine()->getManager();

            $invite = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')->findOneBy(array('id' => $request->get('invite')));

//            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $request->get('chat')));
            $chat = $invite->getChat();
            if (!$chat instanceof \Cron\CronBundle\Entity\Chat){
                //СОздать Chat и добавить двух ChatMember
                $new_chat = new Chat();
                $new_chat->setOwner($invite->getUser1());
                $new_chat->setIsActive(1);
                $em->persist($new_chat);
                $em->flush();

                $this->addChatMember($invite->getUser1(), $new_chat);
                $this->addChatMember($user, $new_chat);
            } else {
                //Просто создать ChatMember
                $this->addChatMember($user, $chat);
            }

            $em->remove($invite);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    private function addChatMember(User $user, Chat $chat){
        if ($chat instanceof \Cron\CronBundle\Entity\Chat && $user instanceof \Cron\CronBundle\Entity\User) {
            $em = $this->getDoctrine()->getManager();

            $chat_member = new ChatMember();
            $chat_member->setChat($chat);
            $chat_member->setUser($user);

            $em->persist($chat_member);
            $em->flush();
        }
    }

    public function declineChatInviteAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $invite = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')->findOneBy(array('id' => $request->get('invite')));
            $em = $this->getDoctrine()->getManager();
            $em->remove($invite);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    public function finishChatAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $request->get('chat'), 'owner' => $request->get("user")));
            $chat->setIsActive(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($chat);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    public function leaveChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $chat_member = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findOneBy(array('chat' => $request->get('chat'), 'user' => $user));
            $em = $this->getDoctrine()->getManager();
            $em->remove($chat_member);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    public function sendChatInviteAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $chat_id = $request->get("chat");
            $user2_id = $request->get("user");

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneById($chat_id);
            if (!$chat instanceof \Cron\CronBundle\Entity\Chat){
                $chat = $this->createChat($user);
                //return new Response('Fail');
            }

            $user2 = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneById($user2_id);
            if (!$user2 instanceof \Cron\CronBundle\Entity\User)
                return new Response('Fail');

            $invite = new ChatInvite();
            $invite->setChat($chat);
            $invite->setUser1($user);
            $invite->setUser2($user2);
            $invite->setInviteDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($invite);
            $em->flush();

            //todo srvmsg

            return new Response('SUCCESS');
        }
    }

    private function createChat(User $me){
        $my_chat = new Chat();
        $my_chat->setOwner($me);
        $my_chat->setIsActive(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($my_chat);
        $em->flush();
        return $my_chat;
    }

    public function deleteChatMemberAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

            $chat_member = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findOneBy(array('chat' => $request->get('chat'), 'user' => $request->get("user")));
            $em = $this->getDoctrine()->getManager();
            $em->remove($chat_member);
            $em->flush();
            
            //todo srvmsg
            
            return new Response('SUCCESS');
        }
    }
}
