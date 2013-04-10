<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cron\CronBundle\Entity\User;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Chat;
use Cron\CronBundle\Entity\ChatInvite;
use Cron\CronBundle\Entity\ChatMember;
use Cron\CronBundle\Entity\ChatMsg;
use Cron\CronBundle\Entity\ChatSrvMsg;

use Cron\CronBundle\Entity\Dialog;
use Cron\CronBundle\Entity\DialogMsg;

class ChatController extends AbstractController
{
    public function loadChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $mychat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array("owner" => $user->getId()));

            if ($mychat instanceof \Cron\CronBundle\Entity\Chat){

                $mychat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array("chat" => $mychat->getId()));

                $mychat->members = $mychat_members;

                $chat_messages = $this->getDoctrine()->getRepository('CronCronBundle:ChatMsg')->findBy(array("chat" => $mychat->getId()), array("msg_date" => "ASC"));

                $mychat->messages = $chat_messages;
            } else {
                $mychat = new Chat();
                $mychat->messages = array();
                $mychat->members = array();
            }



            $income_chats = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array("user" => $user->getId()), array("id" => "DESC"), 3);

            foreach ($income_chats as $id=>$in_chat) {
                $income_chat_messages = $this->getDoctrine()->getRepository('CronCronBundle:ChatMsg')->findBy(array("chat" => $in_chat->getChat()->getId()), array("msg_date" => "ASC"));
                $income_chats[$id]->messages = $income_chat_messages;
            }




            $dialogs = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')
                ->createQueryBuilder('dialog')
                ->where('(dialog.user1 = :uid AND dialog.del1 = 0 AND dialog.spam1 = 0 AND dialog.open1 = 1) OR (dialog.user2 = :uid AND dialog.del2 = 0 AND dialog.spam2 = 0 AND dialog.open2 = 1)')
                ->setParameter('uid', $user->getId())
                ->orderBy('dialog.start_date', 'DESC')
                ->getQuery()
                ->getResult();

            foreach ($dialogs as $id=>$dialog) {
                $messages = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')->findBy(array("dialog" => $dialog->getId()), array("msg_date" => "ASC"));
                $dialogs[$id]->messages = $messages;
            }

            return $this->render("CronCronBundle:Chat:chatwindow.html.twig", array(
                    "mychat" => $mychat,
                    "income_chats" => $income_chats,
                    "dialogs" => $dialogs,
                    "chatlastupdate" => new \DateTime(),
                    "curUser" => $user
                )
            );
        } else {
            return new Response("");
        }
    }

    public function updateChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $dialogs = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')
                ->createQueryBuilder('dialog')
                ->where('(dialog.user1 = :uid AND dialog.del1 = 0 AND dialog.spam1 = 0 AND dialog.open1 = 0) OR (dialog.user2 = :uid AND dialog.del2 = 0 AND dialog.spam2 = 0 AND dialog.open2 = 0)')
                ->setParameter('uid', $user->getId())
                ->orderBy('dialog.start_date', 'DESC')
                ->getQuery()
                ->getResult();

            $new_unreads = 0;

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
                if ($unreads){
                    if ($unreads[0]){
                        $new_unreads += $unreads[0]['unreads'];
                    }
                }
            }

            $invites = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')->findBy(array("user2"=>$user->getId()), array("invite_date"=>"DESC"));
            $invites = count($invites);

            $next_chat_last_update = new \DateTime();

            $srvmsgs = array();

            $chats = array();
            foreach (explode(';', $request->get("chats")) as $chat_id) {
                $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $chat_id));
                if ($chat instanceof Chat){
                    $messages = $this->getDoctrine()->getRepository('CronCronBundle:ChatMsg')
                        ->createQueryBuilder('chat_msg')
                        ->where('chat_msg.chat = :cid AND chat_msg.msg_date > :lid AND chat_msg.user != :myid')
                        ->setParameter('cid', $chat_id)
                        ->setParameter('lid', $request->get("chat_last_update"))
                        ->setParameter('myid', $user->getId())
                        ->orderBy('chat_msg.msg_date', 'ASC')
                        ->getQuery()
                        ->getResult();
                    if (count($messages)){
                        foreach ($messages as $msg) {
                            $chats[$chat_id][$msg->getId()] = array("user_id"=>$msg->getUser()->getId(), "user_name"=>$msg->getUser()->getNick(), "msg_text"=>nl2br($msg->getMsgText()));
                        }
                    }
                    $srv_messages = $this->getDoctrine()->getRepository('CronCronBundle:ChatSrvMsg')
                        ->createQueryBuilder('chat_srvmsg')
                        ->where('chat_srvmsg.chat = :cid AND chat_srvmsg.msg_date > :lid AND chat_srvmsg.to_user = :myid')
                        ->setParameter('cid', $chat_id)
                        ->setParameter('lid', $request->get("chat_last_update"))
                        ->setParameter('myid', $user->getId())
                        ->orderBy('chat_srvmsg.msg_date', 'ASC')
                        ->getQuery()
                        ->getResult();
                    if (count($srv_messages)){
                        foreach ($srv_messages as $msg) {
                            $srvmsgs['chats'][$chat_id][$msg->getId()] = array("msg_text"=>$this->getChatSrvMsg($msg), "msg_text_id"=>$msg->getMsgTextId(), "about_user"=>array('id'=>$msg->getAboutUser()->getId(), 'nick'=>$msg->getAboutUser()->getNick()));
                        }
                    }
                }
            }

            $dialogs = array();
            foreach (explode(';', $request->get("dialogs")) as $dialog_id) {
                $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $dialog_id));
                if ($dialog instanceof Dialog){
                    $messages = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')
                        ->createQueryBuilder('dialog_msg')
                        ->where('dialog_msg.dialog = :did AND dialog_msg.msg_date > :lid AND dialog_msg.user != :myid')
                        ->setParameter('did', $dialog_id)
                        ->setParameter('lid', $request->get("chat_last_update"))
                        ->setParameter('myid', $user->getId())
                        ->orderBy('dialog_msg.msg_date', 'ASC')
                        ->getQuery()
                        ->getResult();
                    if (count($messages)){
                        foreach ($messages as $msg) {
                            $dialogs[$dialog_id][$msg->getId()] = array("user_id"=>$msg->getUser()->getId(), "user_name"=>$msg->getUser()->getNick(), "msg_text"=>nl2br($msg->getMsgText()));
                        }
                    }
                }
            }

            return new Response(json_encode(array(
                "chat_last_update" => $next_chat_last_update->format('Y-m-d H:i:s'),
                "new_dialogs" => $new_unreads,
                "invites" => $invites,
                "chats" => $chats,
                "dialogs" => $dialogs,
                "srvmsgs" => $srvmsgs
            )));
        } else {
            return new Response("");
        }
    }

    public function getChatSrvMsg($srvmsg){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            switch($srvmsg->getMsgTextId()){
                case "1":
                    return sprintf('В чат вошел %s.', $srvmsg->getAboutUser()->getNick());
                    break;
                case "2":
                    return sprintf('В чат добавлен %s.', $srvmsg->getAboutUser()->getNick());
                    break;
                case "3":
                    return sprintf('Чат покинул %s.', $srvmsg->getAboutUser()->getNick());
                    break;
                case "4":
                    return "Вы были удалены из чата.";
                    break;
                case "5":
                    return sprintf('Из чата был удален %s.', $srvmsg->getAboutUser()->getNick());
                    break;
                case "6":
                    return "Чат завершен автором вопроса.";
                    break;
                case "7":
                    return "Чат завершен.";
                    break;
                case "8":
                    return sprintf('%s прекратил общение с вами.', $srvmsg->getAboutUser()->getNick());
                    break;
                default:
                    break;
            }
            return new Response('SUCCESS');
        }
    }

    public function sendChatSrvMsg($type, $params){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

            $srvmsg = new ChatSrvMsg();
            $srvmsg->setMsgDate(new \DateTime());
            switch($type){
                case "IdontWantToTalk":
                    $srvmsg->setDialog($params['dialog'])
                        ->setToUser($params['to_user'])
                        ->setAboutUser($params['about_user'])
                        ->setMsgTextId(8);
                    $em->persist($srvmsg);
                    $em->flush();
                    break;
                case "UserAdded2TheChat":
                    $srvmsg->setChat($params['chat'])
                        ->setToUser($params['chat']->getOwner())
                        ->setAboutUser($params['about_user'])
                        ->setMsgTextId(2);
                    $em->persist($srvmsg);
                    $em->flush();

                    $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array('chat' => $params['chat']->getId()));
                    foreach ($chat_members as $member) {
                        $sub_srvmsg = new ChatSrvMsg();
                        $sub_srvmsg->setMsgDate(new \DateTime())
                            ->setChat($params['chat'])
                            ->setToUser($member->getUser())
                            ->setAboutUser($params['about_user'])
                            ->setMsgTextId(1);
                        $em->persist($sub_srvmsg);
                        $em->flush();
                    }
                    break;
                case "ChatIsFinished":
//                    $srvmsg->setChat($params['chat'])
//                        ->setToUser($params['chat']->getOwner())
//                        ->setMsgTextId(7);
//                    $em->persist($srvmsg);
//                    $em->flush();

                    $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array('chat' => $params['chat']->getId()));
                    foreach ($chat_members as $member) {
                        $sub_srvmsg = new ChatSrvMsg();
                        $sub_srvmsg->setMsgDate(new \DateTime())
                            ->setChat($params['chat'])
                            ->setToUser($member->getUser())
                            ->setAboutUser($member->getUser())
                            ->setMsgTextId(6);
                        $em->persist($sub_srvmsg);
                        $em->flush();
                    }
                    break;
                case "UserLeavesTheChat":
                    $srvmsg->setChat($params['chat'])
                        ->setToUser($params['chat']->getOwner())
                        ->setAboutUser($params['about_user'])
                        ->setMsgTextId(3);
                    $em->persist($srvmsg);
                    $em->flush();

                    $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array('chat' => $params['chat']->getId()));
                    foreach ($chat_members as $member) {
                        $sub_srvmsg = new ChatSrvMsg();
                        $sub_srvmsg->setMsgDate(new \DateTime())
                            ->setChat($params['chat'])
                            ->setToUser($member->getUser())
                            ->setMsgTextId(3);
                        $em->persist($sub_srvmsg);
                        $em->flush();
                    }
                    break;
                case "UserWasKicked":
                    $srvmsg->setChat($params['chat'])
                        ->setToUser($params['chat']->getOwner())
                        ->setAboutUser($params['about_user'])
                        ->setMsgTextId(5);
                    $em->persist($srvmsg);
                    $em->flush();

                    $sub_srvmsg = new ChatSrvMsg();
                    $sub_srvmsg->setMsgDate(new \DateTime())
                        ->setChat($params['chat'])
                        ->setToUser($params['about_user'])
                        ->setAboutUser($params['about_user'])
                        ->setMsgTextId(4);
                    $em->persist($sub_srvmsg);
                    $em->flush();

                    $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array('chat' => $params['chat']->getId()));
                    foreach ($chat_members as $member) {
                        $sub_srvmsg = new ChatSrvMsg();
                        $sub_srvmsg->setMsgDate(new \DateTime())
                            ->setChat($params['chat'])
                            ->setAboutUser($params['about_user'])
                            ->setToUser($member->getUser())
                            ->setMsgTextId(5);
                        $em->persist($sub_srvmsg);
                        $em->flush();
                    }
                    break;
                default:break;
            }
        }
    }

    public function getDialogListAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
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

            return $this->render("CronCronBundle:Chat:dialogs.html.twig", array(
                    "dialogs" => $dialogs,
                    "total_unreads" => $total_unreads,
                    "curUser" => $user
                )
            );
        } else {
            return new Response("");
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

            return new Response('SUCCESS');
        }
    }

    public function getIncomeChatsAction(Request $request){
        if (/*$request->isMethod('POST') && */($user = $this->getUser() instanceof User)){

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
                $dialog->setUser1($user);
                $dialog->setUser2($request->get('to_user'));
                $dialog->setStartDate(new \DateTime());

                $em->persist($dialog);
                $em->flush();
            }
            if ($dialog->getSpam1() || $dialog->getSpam2()){
                $response = '<div class="singleMessage srv-message">
                                <div class="messageText">'.$this->get('translator')->trans('Пользователь не хочет с вами больше говорить').'</div>
                            </div>';
                return new Response($response);
            } else {
                $dialog_msg = new DialogMsg();
                $dialog_msg->setDialog($dialog);
                $dialog_msg->setUser($user);
                $dialog_msg->setMsgDate(new \DateTime());
                $dialog_msg->setMsgText($request->get('message'));
                $dialog_msg->setReadFlag(0);

                $dialog->setDel1(false);
                $dialog->setDel2(false);

                $em->persist($dialog_msg);
                $em->persist($dialog);
                $em->flush();
            }


            return new Response('');
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

                $em = $this->getDoctrine()->getManager();
                $em->persist($chat_msg);
                $em->flush();
            }
            return new Response('SUCCESS');
        }
    }

    public function createDialogAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

            $user2 = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneBy(array('id' => $request->get('to_user')));

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('user1' => $user->getId(), 'user2' => $user2->getId()));
            if (!$dialog instanceof Dialog){
                $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('user2' => $user->getId(), 'user1' => $user2->getId()));
                if (!$dialog instanceof Dialog){
                    $dialog = new Dialog();
                    $dialog->setUser1($user);
                    $dialog->setUser2($user2);
                    $dialog->setOpen1(1);
                    $dialog->setOpen2(0);
                    $dialog->setDel1(0);
                    $dialog->setDel2(0);
                    $dialog->setSpam1(0);
                    $dialog->setSpam2(0);
                    $dialog->setStartDate(new \DateTime());

                    $em->persist($dialog);
                    $em->flush();
                }
            }

            return new Response($dialog->getId());
        }
    }

    public function openDialogAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

            $dialog = array();
            if ($request->get('dialog')>0){
                $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            }

            if (!$dialog instanceof Dialog){
                $dialog = new Dialog();
                $dialog->setUser1($user);
                $user2 = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneBy(array('id' => $request->get('to_user')));
                $dialog->setUser2($user2);
                $dialog->setStartDate(new \DateTime());

                $em->persist($dialog);
                $em->flush();
            }

            if ($dialog->getUser1() == $user){
                $dialog->setOpen1(1);
            } elseif ($dialog->getUser2() == $user){
                $dialog->setOpen2(1);
            }

//            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            $messages = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')
                ->createQueryBuilder('dialog_msg')
                ->where('dialog_msg.dialog = :did')
                ->setParameter('did', $dialog->getId())
                ->orderBy('dialog_msg.msg_date', 'ASC')
                ->getQuery()
                ->getResult();

            $html = '<div class="chat-content" tab="d'.$dialog->getId().'" data-dialog-id="'.$dialog->getId().'" data-to-user="'.($dialog->getUser1()==$user ? $dialog->getUser2()->getId() : $dialog->getUser1()->getId()).'"><div class="chat"><div style="border-right: 2px solid #465176">';
            foreach ($messages as $message) {
                $html .= '<div class="singleMessage'.($message->getUser()!=$user?:' my-message').'">
                            <div class="chatUsername">'.$message->getUser()->getNick().'</div>
                            <div class="messageText">'.$message->getMsgText().'</div>
                        </div>';
            }
            $html .='</div></div></div>';
            return new Response($html);
        }
    }

    public function closeDialogAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog->getUser1() == $user){
                $dialog->setOpen1(0);
            } elseif ($dialog->getUser2() == $user){
                $dialog->setOpen2(0);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            return new Response('SUCCESS');
        }
    }

    public function readDialogMsgsAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

            $dialog_msgs = $this->getDoctrine()->getRepository('CronCronBundle:DialogMsg')->findBy(array('dialog' => $request->get('dialog')));
            foreach ($dialog_msgs as $dialog_msg) {
                if ($dialog_msg->getUser()!=$user){
                    $dialog_msg->setReadFlag(1);
                    $em->persist($dialog_msg);
                }
            }

            $em->flush();

            return new Response('SUCCESS');
        }
    }

    public function deleteDialogAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $to_user = null;

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog->getUser1() == $user){
                $dialog->setDel1(1);
                $to_user = $dialog->getUser2();
            } elseif ($dialog->getUser2() == $user){
                $dialog->setDel2(1);
                $to_user = $dialog->getUser1();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            $this->sendChatSrvMsg('IdontWantToTalk', array("dialog"=>$dialog, "to_user"=>$to_user, "about_user"=>$user));

            return new Response('SUCCESS');
        }
    }

    public function checkDialogAsSpamAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $to_user = null;

            $dialog = $this->getDoctrine()->getRepository('CronCronBundle:Dialog')->findOneBy(array('id' => $request->get('dialog')));
            if ($dialog->getUser1() == $user){
                $dialog->setSpam1(1);
                $to_user = $dialog->getUser2()->getId();
            } elseif ($dialog->getUser2() == $user){
                $dialog->setSpam2(1);
                $to_user = $dialog->getUser1()->getId();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($dialog);
            $em->flush();

            $this->sendChatSrvMsg('IdontWantToTalk', array("dialog"=>$dialog, "to_user"=>$to_user, "about_user"=>$user));

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

            $this->sendChatSrvMsg('UserAdded2TheChat', array("chat"=>$chat, "about_user"=>$user));

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

            return new Response('SUCCESS');
        }
    }

    public function finishChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array('id' => $request->get('chat'), 'owner' => $user->getId()));
            $chat->setIsActive(0);
            $em->persist($chat);
//            $em->flush();

            $this->sendChatSrvMsg('ChatIsFinished', array("chat"=>$chat));

            $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array('chat' => $chat->getId()));
            foreach ($chat_members as $member) {
                $em->remove($member);
            }

            $chat_invites = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')->findBy(array('chat' => $chat->getId()));
            foreach ($chat_invites as $invite) {
                $em->remove($invite);
            }
//            $em->remove($chat_invites);
            $em->flush();


            $response = '<div class="singleMessage srv-message">
                                <div class="messageText">'.$this->get('translator')->trans('Чат завершен').'</div>
                            </div>';
            return new Response($response);
        }
    }

    public function leaveChatAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $chat_member = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findOneBy(array('chat' => $request->get('chat'), 'user' => $user->getId()));
            if ($chat_member instanceof ChatMember){
                $em = $this->getDoctrine()->getManager();
                $em->remove($chat_member);
                $em->flush();

                $this->sendChatSrvMsg('UserLeavesTheChat', array("chat"=>$chat_member->getChat(), "about_user"=>$user));

                $response = '<div class="singleMessage srv-message">
                                <div class="messageText">'.$this->get('translator')->trans('Вы вышли из чата').'</div>
                            </div>';
                return new Response($response);
            }
            return new Response('');
        }
    }

    public function kickUserAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){

            $chat_member = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findOneBy(array('chat' => $request->get('chat'), 'user' => $request->get('user')));
            $em = $this->getDoctrine()->getManager();
            $em->remove($chat_member);
            $em->flush();

            $this->sendChatSrvMsg('UserWasKicked', array("chat"=>$chat_member->getChat(), "to_user"=>$user, "about_user"=>$chat_member->getUser()));

            return new Response('SUCCESS');
        }
    }

    public function sendChatInviteAction(Request $request){
        $user = $this->getUser();
        if (/*$request->isMethod('POST') && */($user instanceof User)){
            $em = $this->getDoctrine()->getManager();

//            $chat_id = $request->get("chat");
            $user2_id = $request->get("user");

            $chat = $this->getDoctrine()->getRepository('CronCronBundle:Chat')->findOneBy(array("owner" => $user->getId()));
            if (!$chat instanceof \Cron\CronBundle\Entity\Chat){
                $chat = $this->createChat($user);
                //return new Response('Fail');
            }
            $chat->setIsActive(1);
            $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->find($request->get('question'));
            $chat->setQuestion($question);
            $em->persist($chat);

            $chat_members = $this->getDoctrine()->getRepository('CronCronBundle:ChatMember')->findBy(array("chat" => $chat->getId()));

            if (count($chat_members)>10){
                return new Response('Fail');
            }

            $user2 = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneById($user2_id);
            if (!$user2 instanceof \Cron\CronBundle\Entity\User)
                return new Response('Fail');

            $invite = $this->getDoctrine()->getRepository('CronCronBundle:ChatInvite')->findOneBy(array("chat" => $chat->getId(), "user1" => $user->getId(), "user2" => $user2->getId()));
            if (!$invite instanceof \Cron\CronBundle\Entity\ChatInvite){
                $invite = new ChatInvite();
                $invite->setChat($chat);
                $invite->setUser1($user);
                $invite->setUser2($user2);
                $invite->setInviteDate(new \DateTime());

                $em->persist($invite);

            }

            $em->flush();


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

}
