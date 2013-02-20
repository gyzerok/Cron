<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\Article;
use Cron\CronBundle\Entity\ArticleCategory;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Entity\Feedback;
use Cron\CronBundle\Entity\AdminSettings;
use Cron\CronBundle\Form\NewArticle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller implements InitializableControllerInterface
{
    protected $onlineUserCount;
    protected $totalUserCount;

    public function initialize(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $em = $this->getDoctrine()->getManager();
        $sid = $request->getSession()->getId();
        $isOnline = $this->getDoctrine()->getRepository('CronCronBundle:Online')->findBySid($sid);
        if (empty($isOnline))
        {
            $onlineEntry = new \Cron\CronBundle\Entity\Online($sid);
            $em->persist($onlineEntry);
        }

        $timeBoundary = new \DateTime();
        $timeBoundary->sub(new \DateInterval('PT15M'));
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

    public function newarticleAction(Request $request, $article_id)
    {
        $user = $this->getUser();

        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
        }

        if ($request->isMethod('post')){

            $em = $this->getDoctrine()->getManager();

            $post_data = $request->get('article');

            $category = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->find($post_data['category']);

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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
        }

        $questions = array();
        if ($tab=='all'){
            $questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array(), array("datetime"=>"DESC"));
        } elseif ($tab=='spam'){
            // todo refactor it
            $all_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findAll();
            foreach ($all_questions as $quest) {
                if (count($quest->getSpams())>0){
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
        }

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($request->get("question"));
        //todo delete answers & notes
        $em = $this->getDoctrine()->getManager();
        $em->remove($question);
        $em->flush();
        return new Response("SUCCESS");
    }

    public function answersAction(Request $request, $tab)
    {
        $user = $this->getUser();
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
        }

        $answers = array();
        if ($tab=='all'){
            $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array(), array("pubDate"=>"DESC"));
        } elseif ($tab=='spam'){
            // todo refactor it
            $all_answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findAll();
            foreach ($all_answers as $answer) {
                if (count($answer->getSpams())>0){
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return new Response("Fail");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
        if (!$user instanceof User || $user->getRole() < 2) {
            return $this->redirect("/");
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
                ->where('d.spam1 = 1 OR d.spam2 = 1')
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

}
