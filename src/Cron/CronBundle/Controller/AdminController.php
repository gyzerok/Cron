<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\Article;
use Cron\CronBundle\Entity\ArticleCategory;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\Notepad;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Entity\Feedback;
use Cron\CronBundle\Entity\AdminSettings;
use Cron\CronBundle\Form\NewArticle;
use Cron\CronBundle\Entity\Chat;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    public function newarticleAction(Request $request, $article_id)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        if ($request->isMethod('post')){

            $em = $this->getDoctrine()->getManager();

            $post_data = $request->get('article');

            if (!empty($post_data['category'])){
                $category = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->find($post_data['category']);
            } else {
                $category = new ArticleCategory();
                if (!empty($post_data['new_category'])){
                    $category->setName($post_data['new_category']);
                    $em->persist($category);
                    $em->flush();
                } else {
                    return $this->redirect("/admin/newarticle");
                }
            }

            $imgs = array();

            if ($article_id!=''){
                $article = $this->getDoctrine()->getRepository("CronCronBundle:Article")->find($article_id);
            } else {
                $article = new Article();
            }
            $article->setHeader($post_data['header'])
                ->setLocale($post_data['locale'])
                ->setCategory($category)
                ->setText($post_data['text'])
                ->setLinkType($post_data['link_type'])
                ->setLinkValue($post_data['link_value'])
                ->setDatetime(new \DateTime());

            $em->persist($article);
            $em->flush();

            if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/articles_i/'))
                mkdir($_SERVER['DOCUMENT_ROOT'].'/articles_i/');
            $files_dir = $_SERVER['DOCUMENT_ROOT'].'/articles_i/'.$article->getId().'/';
            if (!is_dir($files_dir))
                mkdir($files_dir);

            foreach ($request->files->get('article') as $id=>$file) {
                if (!empty($file)){
                    $file->move($files_dir, $id.'.jpg');
                    $imgs[$id] = true;
                }
            }
            if ($article_id!=''){
                if (!empty($imgs)){
                    $article->setImgs($imgs);
                }
            } else {
                $article->setImgs($imgs);
            }
            $em->persist($article);
            $em->flush();
            return $this->redirect("/admin/articles");
        }
        $form = $this->createForm(new NewArticle());
        if ($article_id!=''){
            $cur_article = $this->getDoctrine()->getRepository("CronCronBundle:Article")->find($article_id);
            $form->setData(array("header"=>$cur_article->getHeader(), "locale"=>$cur_article->getLocale(), "category"=>$cur_article->getCategory(), "text"=>$cur_article->getText(), "link_type"=>$cur_article->getLinkType(), "link_value"=>$cur_article->getLinkValue()));
        }

        return $this->render("CronCronBundle:Admin:newarticle.html.twig", array('title' => 'Новая статья',
            'curUser' => $this->getUser(),
            'form' => $form->createView(),
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function articlesAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $articles = $this->getDoctrine()->getRepository("CronCronBundle:Article")->findBy(array(), array("datetime"=>"DESC"));

        return $this->render("CronCronBundle:Admin:articles.html.twig", array('title' => 'Статьи',
            'articles' => $articles,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function deleteArticleAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $article = $this->getDoctrine()->getRepository("CronCronBundle:Article")->find($request->get("article"));
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function questionsAction(Request $request, $tab)
    {
        $user = $this->getUser();

        $questions = array();
        if ($tab=='all'){
            $questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("isSpam"=>false), array("datetime"=>"DESC"));
            if ($user->getRole()<2){
                $this->redirect("/");
            }
        } elseif ($tab=='spam'){
            if ($user->getRole()<1){
                $this->redirect("/");
            }
            $all_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("isSpam"=>false));
            foreach ($all_questions as $id=>$quest) {
                if (count($quest->getSpams())>0){
                    $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user"=>$quest->getUser()->getId()));
                    $quest->question_count = count($user_questions);
                    array_push($questions, $quest);
                }
            }
        }
        return $this->render("CronCronBundle:Admin:questions.html.twig", array('title' => 'Вопросы',
            'questions' => $questions,
            'tab' => $tab,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function deleteQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<1){
            $this->redirect("/");
        }
        $em = $this->getDoctrine()->getManager();

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($request->get('question'));
        $note_questions = $this->getDoctrine()->getRepository("CronCronBundle:NotesQuestion")->findBy(array("question"=>$question->getId()));
        $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$question->getId()));

        $chat = $this->getDoctrine()->getRepository("CronCronBundle:Chat")->findOneBy(array("question"=>$question->getId()));
        if ($chat instanceof Chat)
        {
            $chats_msgs = $this->getDoctrine()->getRepository("CronCronBundle:ChatMsg")->findBy(array("chat"=>$chat->getId()));
            $chats_srvmsgs = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("chat"=>$chat->getId()));
            $chats_membering = $this->getDoctrine()->getRepository("CronCronBundle:ChatMember")->findBy(array("chat"=>$chat->getId()));
            $chats_invites = $this->getDoctrine()->getRepository("CronCronBundle:ChatInvite")->findBy(array("chat"=>$chat->getId()));

            foreach ($chats_invites as $chats_invites1) {
                $em->remove($chats_invites1);
            }
            foreach ($chats_membering as $chats_membering1) {
                $em->remove($chats_membering1);
            }
            foreach ($chats_msgs as $chats_msgs1) {
                $em->remove($chats_msgs1);
            }
            foreach ($chats_srvmsgs as $chats_srvmsgs1) {
                $em->remove($chats_srvmsgs1);
            }
            $em->remove($chat);
            $em->flush();
        }

        foreach ($note_questions as $note_questions1) {
            $em->remove($note_questions1);
        }
        foreach ($answers as $answers1) {
            $em->remove($answers1);
        }

        $em->remove($question);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function confirmSpamQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<1){
            $this->redirect("/");
        }

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($request->get("question"));
        $question->setSpams(null);
        $question->setIsSpam(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($question);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function cancelSpamQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<1){
            $this->redirect("/");
        }

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findOneBy(array("id" => $request->get("question"), "amnestied" => false));
        $question->setSpams(null);
        $question->setAmnestied(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($question);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function answersAction(Request $request, $tab)
    {
        $user = $this->getUser();

        $answers = array();
        if ($tab=='all'){
            if ($user->getRole()<2){
                $this->redirect("/");
            }
            $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("isSpam"=>false), array("pubDate"=>"DESC"));
        } elseif ($tab=='spam'){
            if ($user->getRole()<1){
                $this->redirect("/");
            }
            $all_answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("isSpam"=>false));
            foreach ($all_answers as $answer) {
                if (count($answer->getSpams())>0){
                    $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user"=>$answer->getUser()->getId()));
                    $answer->question_count = count($user_questions);
                    array_push($answers, $answer);
                }
            }
        }
        return $this->render("CronCronBundle:Admin:answers.html.twig", array('title' => 'Ответы',
            'answers' => $answers,
            'tab' => $tab,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function deleteAnswerAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $answer = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->find($request->get("answer"));

        $em = $this->getDoctrine()->getManager();
        $em->remove($answer);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function confirmSpamAnswerAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<1){
            $this->redirect("/");
        }

        $answer = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->find($request->get("answer"));
        $answer->setSpams(null);
        $answer->setIsSpam(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($answer);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function cancelSpamAnswerAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<1){
            $this->redirect("/");
        }

        $answer = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findOneBy(array("id" => $request->get("answer"), "amnestied" => false));
        $answer->setSpams(null);
        $answer->setAmnestied(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($answer);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function sendFeedbackAction(Request $request)
    {
        $user = $this->getUser();

        $feedback = new Feedback();
        $feedback->setType($request->get('type'))
            ->setText($request->get('text'))
            ->setDatetime(new \DateTime());
        if ($user instanceof User){
            $feedback->setUser($user);
            $feedback->setEmail($user->getUsername());
        } else {
            $feedback->setEmail($request->get('email'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($feedback);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function replyFeedbackAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $feedback = $this->getDoctrine()->getRepository("CronCronBundle:Feedback")->find($request->get('feedback'));

        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance(null, null, "text/html")
            ->setSubject('Ответ на сообщение "'.$feedback->getText().'"')
            ->setFrom("aditus777@gmail.com")
            ->setTo($feedback->getEmail())
            ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' . $request->get('text'));
        $mailer->send($message);

        $feedback->setAnswered(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($feedback);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function deleteFeedbackAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $feedback = $this->getDoctrine()->getRepository("CronCronBundle:Feedback")->find($request->get("feedback"));
        $em = $this->getDoctrine()->getManager();
        $em->remove($feedback);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function appealsAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $feedback = $this->getDoctrine()->getRepository("CronCronBundle:Feedback")->findBy(array("type"=>"appeal", "answered"=>0), array("datetime"=>"DESC"));

        return $this->render("CronCronBundle:Admin:support.html.twig", array('title' => 'Жалобы',
            'feedback' => $feedback,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function ideasAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $feedback = $this->getDoctrine()->getRepository("CronCronBundle:Feedback")->findBy(array("type"=>"idea", "answered"=>0), array("datetime"=>"DESC"));

        return $this->render("CronCronBundle:Admin:support.html.twig", array('title' => 'Предложения',
            'feedback' => $feedback,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function srvmsgAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $srvmsg = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);
        if (!$srvmsg instanceof AdminSettings){
            $srvmsg = new AdminSettings();
            $srvmsg->setCreditCurrency(5)
                ->setAnswers50(5)
                ->setAnswers100(10)
                ->setAnswers1000(50);
        }

        if ($request->isMethod('post')){
            $srvmsg->setSrvmsg($request->get('srvmsg'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($srvmsg);
            $em->flush();
        }
        return $this->render("CronCronBundle:Admin:srvmsg.html.twig", array('title' => 'Сервисное сообщение',
            'srvmsg' => $srvmsg->getSrvmsg(),
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function getHeaderSrvmsgAction(Request $request)
    {
        $srvmsg = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);
        if (!$srvmsg instanceof AdminSettings){
            $srvmsg = new AdminSettings();
        }

        return new Response($srvmsg->getSrvmsg());
    }

    public function creditsAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $admin_settings = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);
        if (!$admin_settings instanceof AdminSettings){
            $admin_settings = new AdminSettings();
            $admin_settings->setCreditCurrency(5)
                ->setAnswers50(5)
                ->setAnswers100(10)
                ->setAnswers1000(50);
        }

        if ($request->isMethod('post')){
            $admin_settings->setCreditCurrency($request->get('credit_currency'))
                ->setAnswers50($request->get('answers50'))
                ->setAnswers100($request->get('answers100'))
                ->setAnswers1000($request->get('answers1000'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($admin_settings);
            $em->flush();
        }
        return $this->render("CronCronBundle:Admin:credits.html.twig", array('title' => 'Настройки кредитов',
            'credits_settings' => $admin_settings,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function usersAction(Request $request, $tab)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $users = array();
        if ($tab=='all'){
            $users = $this->getDoctrine()->getRepository("CronCronBundle:User")->findBy(array("isActive"=>"1"), array("regDate"=>"DESC"));
            foreach ($users as $id => $user1) {
                $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user" => $user1->getId()));
                $users[$id]->questions = count($user_questions);
            }
        } elseif ($tab=='spam'){
            $spam_dialogs = $this->getDoctrine()->getRepository("CronCronBundle:Dialog")
                ->createQueryBuilder('d')
                ->where('(d.spam1 = 1 AND d.ignore1 = 0) OR (d.spam2 = 1 AND d.ignore2 = 0)')
                ->getQuery()
                ->getResult();/*->findBy(array("isActive"=>"1"), array("startdate"=>"DESC"));*/
            foreach ($spam_dialogs as $spam_d) {
                if ($spam_d->getSpam1()){
                    $spam_user = $spam_d->getUser2();
                    $spam_user->marked_by = $spam_d->getUser1();
                } elseif($spam_d->getSpam2()){
                    $spam_user = $spam_d->getUser1();
                    $spam_user->marked_by = $spam_d->getUser2();
                }
                $spam_user->dialog = $spam_d->getId();
                array_push($users, $spam_user);
            }

            foreach ($users as $id => $user1) {
                $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user" => $user1->getId()));
                $users[$id]->questions = count($user_questions);
            }
        }


        return $this->render("CronCronBundle:Admin:users.html.twig", array('title' => 'Пользователи',
            'users' => $users,
            'tab' => $tab,
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function blockUserAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $user4block = $this->getDoctrine()->getRepository("CronCronBundle:User")->find($request->get("user"));
        $bunDate = new \DateTime();
        $bunDate->modify("+60 minutes");
        $user4block->setLockedTill($bunDate);

        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance(null, null, "text/html")
            ->setSubject('Ваш аккаунт заблокирован')
            ->setFrom("aditus777@gmail.com")
            ->setTo($user4block->getUsername())
            ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' .
                'Здравствуйте, '.$user4block->getNick().'!<br>' .
                'Ваш акаунт  автоматически заблокирован на 60 минут в следствии нарушения правил ресурса.!<br>' .
                'Пожалуйста, ознакомьтесь с <a href="http://aditus.ru/agreement">пользовательским соглашением</a> и <a href="http://aditus.ru/rules">правилами</a> ADITUS.ru<br>' .
                'Если у вас есть вопросы или вам необходима помощь, вы можете обратиться в службу поддержки ADITUS.ru<br>' .
                '</body></html>');
        $mailer->send($message);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user4block);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function deleteUserAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $user_id = $request->get("user");
        
        $user2del = $this->getDoctrine()->getRepository("CronCronBundle:User")->find($user_id);

        $user_settings = $this->getDoctrine()->getRepository("CronCronBundle:UserSettings")->findOneBy(array("user"=>$user_id));
        $notepad = $this->getDoctrine()->getRepository("CronCronBundle:Notepad")->findOneBy(array("user"=>$user_id));
        $links = $this->getDoctrine()->getRepository("CronCronBundle:UserLink")->findBy(array("user"=>$user_id));
        $notes_questions = $this->getDoctrine()->getRepository("CronCronBundle:NotesQuestion")->findBy(array("user"=>$user_id));
        $notes_articles = $this->getDoctrine()->getRepository("CronCronBundle:NotesArticle")->findBy(array("user"=>$user_id));

        $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("user"=>$user_id));
        $questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user"=>$user_id));

        $files = $this->getDoctrine()->getRepository("CronCronBundle:File")->findBy(array("user"=>$user_id));
        $files_dir = $_SERVER['DOCUMENT_ROOT'].'/files/'.$user_id.'/';
        foreach ($files as $file) {
            @unlink($files_dir.$file->getFilename());
        }
        @rmdir($files_dir);

        $dialogs_msgs = $this->getDoctrine()->getRepository("CronCronBundle:DialogMsg")->findBy(array("user"=>$user_id));
        $dialogs1 = $this->getDoctrine()->getRepository("CronCronBundle:Dialog")->findBy(array("user1"=>$user_id));
        $dialogs2 = $this->getDoctrine()->getRepository("CronCronBundle:Dialog")->findBy(array("user2"=>$user_id));

        $chats_msgs = $this->getDoctrine()->getRepository("CronCronBundle:ChatMsg")->findBy(array("user"=>$user_id));
        $chats_membering = $this->getDoctrine()->getRepository("CronCronBundle:ChatMember")->findBy(array("user"=>$user_id));
        $chats_invite1 = $this->getDoctrine()->getRepository("CronCronBundle:ChatInvite")->findBy(array("user1"=>$user_id));
        $chats_invite2 = $this->getDoctrine()->getRepository("CronCronBundle:ChatInvite")->findBy(array("user2"=>$user_id));
        $chats = $this->getDoctrine()->getRepository("CronCronBundle:Chat")->findBy(array("owner"=>$user_id));

        $feedposts = $this->getDoctrine()->getRepository("CronCronBundle:Feedback")->findBy(array("user"=>$user_id));

        $em = $this->getDoctrine()->getManager();
        if ($user_settings instanceof UserSettings)
            $em->remove($user_settings);
        if ($notepad instanceof Notepad)
            $em->remove($notepad);
        foreach ($links as $links1) {
            $em->remove($links1);
        }
        foreach ($notes_questions as $notes_questions1) {
            $em->remove($notes_questions1);
        }
        foreach ($notes_articles as $notes_articles1) {
            $em->remove($notes_articles1);
        }
        foreach ($answers as $answers1) {
            $em->remove($answers1);
        }
        $em->flush();
        foreach ($questions as $questions1) {
            $answers_on_this = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$questions1->getId()));
            foreach ($answers_on_this as $answers_on_this1) {
                $em->remove($answers_on_this1);
            }
            $em->flush();

            $em->remove($questions1);
        }
        foreach ($files as $files1) {
            $em->remove($files1);
        }
        foreach ($dialogs_msgs as $dialogs_msgs1) {
            $em->remove($dialogs_msgs1);
        }
        $em->flush();
        foreach ($dialogs1 as $dialogs1_1) {
            $msgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:DialogMsg")->findBy(array("dialog"=>$dialogs1_1->getId()));
            foreach ($msgs_in_this as $msgs_in_this1) {
                $em->remove($msgs_in_this1);
            }
            $em->flush();

            $srvmsgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("dialog"=>$dialogs1_1->getId()));
            foreach ($srvmsgs_in_this as $srvmsgs_in_this1) {
                $em->remove($srvmsgs_in_this1);
            }
            $em->flush();

            $em->remove($dialogs1_1);
        }
        foreach ($dialogs2 as $dialogs2_1) {
            $msgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:DialogMsg")->findBy(array("dialog"=>$dialogs2_1->getId()));
            foreach ($msgs_in_this as $msgs_in_this1) {
                $em->remove($msgs_in_this1);
            }
            $em->flush();

            $srvmsgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("dialog"=>$dialogs2_1->getId()));
            foreach ($srvmsgs_in_this as $srvmsgs_in_this1) {
                $em->remove($srvmsgs_in_this1);
            }
            $em->flush();

            $em->remove($dialogs2_1);
        }
        foreach ($chats_msgs as $chats_msgs1) {
            $em->remove($chats_msgs1);
        }
        foreach ($chats_membering as $chats_membering1) {
            $em->remove($chats_membering1);
        }
        foreach ($chats_invite1 as $chats_invite1_1) {
            $em->remove($chats_invite1_1);
        }
        foreach ($chats_invite2 as $chats_invite2_1) {
            $em->remove($chats_invite2_1);
        }
        $em->flush();
        foreach ($chats as $chats1) {
            $msgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:ChatMsg")->findBy(array("chat"=>$chats1->getId()));
            foreach ($msgs_in_this as $msgs_in_this1) {
                $em->remove($msgs_in_this1);
            }
            $em->flush();

            $members_in_this = $this->getDoctrine()->getRepository("CronCronBundle:ChatMember")->findBy(array("chat"=>$chats1->getId()));
            foreach ($members_in_this as $members_in_this1) {
                $em->remove($members_in_this1);
            }
            $em->flush();

            $srvmsgs_in_this = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("chat"=>$chats1->getId()));
            foreach ($srvmsgs_in_this as $srvmsgs_in_this1) {
                $em->remove($srvmsgs_in_this1);
            }
            $em->flush();

            $em->remove($chats1);
        }
        foreach ($feedposts as $feedposts1) {
            $em->remove($feedposts1);
        }
        $em->flush();

        $srvmsgs_to_user = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("to_user"=>$user_id));
        foreach ($srvmsgs_to_user as $srvmsgs_to_user1) {
            $em->remove($srvmsgs_to_user1);
        }
        $srvmsgs_about_user = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("about_user"=>$user_id));
        foreach ($srvmsgs_about_user as $srvmsgs_about_user1) {
            $em->remove($srvmsgs_about_user1);
        }
        $em->flush();

        $em->remove($user2del);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function ignoreSpamDialogAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $dialog = $this->getDoctrine()->getRepository("CronCronBundle:Dialog")->find($request->get("dialog"));
        if ($dialog->getSpam1()){
            $dialog->setIgnore1(1);
        } else {
            $dialog->setIgnore2(1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($dialog);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function changeCreditsAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRole()<2){
            $this->redirect("/");
        }

        $victim_user = $this->getDoctrine()->getRepository("CronCronBundle:User")->find($request->get("user"));
        $victim_user->setCredits($request->get("credits"));

        $em = $this->getDoctrine()->getManager();
        $em->persist($victim_user);
        $em->flush();
        return new Response("SUCCESS");
    }

    //todo watchSpamDialog

}
