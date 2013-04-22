<?php

namespace Cron\CronBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\Category;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Form\NewQuestion;
use Cron\CronBundle\Form\NewAnswer;
use Cron\CronBundle\Form\Registration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    public function indexAction(Request $request)
    {
        if ($request->get('reg_success')){
            $show_success_msg = true;
        } else {
            $show_success_msg = false;
        }

        $numAnswers = array(10 => '10', 20 => '20');
        if ($this->getUser() instanceof User)
            $numAnswers = array(10 => '10', 20 => '20', 50 => '50', 100 => '100', 1000 => '1000');

        $question = new Question();
        $form = $this->createForm(new NewQuestion($this->container->get('session'), $this->getUser() instanceof User, $numAnswers), $question);

        if ($request->isMethod('POST'))
        {
            $form->bind($request);

            if ($form->isValid())
            {
                $user = $this->getUser();
                if (!$user instanceof User)
                {
                    $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');

                    if ($question->getBoundary() > 20)
                        $question->setBoundary(20);
                }

                $question->setUser($user);
                $question->setUserIp($this->container->get('request')->getClientIp());

                if ($question->getBoundary() > 20){
                    if ($user->getCredits() < 5){
                        return $this->redirect($this->generateUrl('index'));
                    }
                    $admin_settings = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);
                    $price = 5;
                    switch($question->getBoundary()){
                        case '50':
                            $price = $admin_settings->getAnswers50();
                            break;
                        case '100':
                            $price = $admin_settings->getAnswers100();
                            break;
                        case '1000':
                            $price = $admin_settings->getAnswers1000();
                            break;
                        default:
                            break;
                    }
                    $user->setCredits($user->getCredits()-$price);
                }


                if ($question->getPrivate()){
                    $user->setCredits($user->getCredits()-5);
                }

                $locale = $request->getSession()->get('_locale');
                if (!$locale){
                    $locale = 'ru_RU';
                }
                $question->setLocale($locale);

                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }
        }

        $user = $this->getUser();
        $userQuestions = null;

        if ($user instanceof User){
            $userQuestions = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findAllbyUser($user);
        } else {
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
            $userQuestions = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findBy(array("user"=>$user->getId(), "user_ip"=>$this->container->get('request')->getClientIp(), "hide_on_index"=>0));
        }

        return $this->render("CronCronBundle:Main:index.html.twig", array('title' => 'Главная',
                                                                          'curUser' => $this->getUser(),
                                                                          'userQuestions' => $userQuestions,
                                                                          'form' => $form->createView(),
                                                                          'show_success_msg' => $show_success_msg,
                                                                          'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
        );

    }

    public function categoryAction($category_id, Request $request)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $answer = new Answer();
        $form = $this->createForm(new NewAnswer(), $answer);
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('category'));
            }
        }

        $viewbytime = new \DateTime();
        $view_cats = array();
        $view_locale = array('ru', 'en', 'pt');
        $income_locale = array('ru', 'en', 'pt');
        $income_cats = array();
        if ($user instanceof User){
            $my_settings = $user->getSettings();
            if ($my_settings instanceof UserSettings){
                switch($my_settings->getViewByTime()){
                    case 'day':
                        $viewbytime->modify("-1 day");
                        break;
                    case 'week':
                        $viewbytime->modify("-1 week");
                        break;
                    case 'month':
                        $viewbytime->modify("-1 month");
                        break;
                    case 'all':
                    default:
                        $viewbytime->modify("-100 years");
                        break;
                }
                if ($my_settings->getViewCats()){
                    foreach ($my_settings->getViewCats() as $id=>$view_cat) {
                        array_push($view_cats, $id);
                    }
                }
                if ($my_settings->getViewLocale()){
                    $view_locale = array();
                    foreach ($my_settings->getViewLocale() as $id=>$view_loc) {
                        if ($view_loc){
                            array_push($view_locale, $id);
                        }
                    }
                }
                if ($my_settings->getIncomeCats()){
                    foreach ($my_settings->getIncomeCats() as $id=>$income_cat) {
                        array_push($income_cats, $id);
                    }
                }
                if ($my_settings->getIncomeLocale()){
                    $income_locale = array();
                    foreach ($my_settings->getIncomeLocale() as $id=>$income_loc) {
                        if ($income_loc){
                            array_push($income_locale, $id);
                        }
                    }
                }
            } else {
                $viewbytime->modify("-100 years");
            }
        } else {
            $viewbytime->modify("-100 years");
        }



        if ($category_id>0){ //single category
            $categorized = array();
            $categorized[0] = $this->getDoctrine()->getRepository("CronCronBundle:Category")->find($category_id);
            $questions = array();
            if (!empty($view_locale)){
                $questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                ->createQueryBuilder('question')
                ->where('question.category = :cid AND question.status '.($user instanceof User ? '<>': '=').' :status AND question.datetime > :viewbytime  AND question.isSpam = false AND question.user <> :user AND question.locale IN (:locale)')
                ->setParameter('cid', $category_id)
                ->setParameter('status', '2')
                ->setParameter('viewbytime', $viewbytime->format("Y-m-d H:i:s"))
                ->setParameter('user', ($user instanceof User ? $user->getId(): 0))
                ->setParameter('locale', $view_locale)
                ->orderBy('question.datetime', 'DESC')
                ->getQuery()
                ->getResult();
            }
            if ($user instanceof User){
                foreach ($questions as $qid=>$question) {
                    if ($question->getSpams()->contains($user) || $user->getIgnoredQuestions()->contains($question) || !$this->geoFilterQuestion($user, $question) || $this->IAnsweredThisQuestion($user, $question)){
                        unset($questions[$qid]);
                    }
                }
            }

            $categorized[0]->questions = $questions;
        } elseif ($category_id<0) { //income questions

            $categorized = array();
            $income = new Category();
            $income->setName("Входящие вопросы");
            if (!$user instanceof User){
                $my_answers = array();
            } else {
                $my_answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("user"=>$user->getId(), "hide_income"=>0), array("pubDate"=>"DESC"));
            }

            $income->questions = array();
            foreach ($my_answers as $my_answer) {
                $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($my_answer->getQuestion()->getId());
                if (!$question->getSpams()->contains($user)){
                    $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$question->getId()), array("pubDate"=>"DESC"));
                    $question->answers = $answers;
                    array_push($income->questions, $question);
                }
            }
            $categorized[0] = $income;

        } else { //all categories

            if ($user instanceof User){
                $user->setLastCatsView(new \DateTime());

                $em->persist($user);
                $em->flush();
            }

            if (!empty($income_cats)){
                $categorized = $this->getDoctrine()->getRepository("CronCronBundle:Category")
                    ->createQueryBuilder('category')
                    ->where('category.id IN (:cid)')
                    ->setParameter('cid', $income_cats)
                    ->getQuery()
                    ->getResult();
            } else {
                $categorized = $this->getDoctrine()->getRepository("CronCronBundle:Category")->findAll();
            }

            foreach ($categorized as $id=>$category) {
                if ($category->getId()==1){
                    unset($categorized[$id]);
                } else {
                    $questions = array();
                    if (!empty($income_locale)){
                        $questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                            ->createQueryBuilder('question')
                            ->where('question.category = :cid AND question.status '.($user instanceof User ? '<>': '=').' :status AND question.isSpam = false AND question.user <> :user AND question.locale IN (:locale)')
                            ->setParameter('cid', $category->getId())
                            ->setParameter('status', '2')
//                        ->setParameter('viewbytime', $viewbytime->format("Y-m-d H:i:s"))
                            ->setParameter('user', ($user instanceof User ? $user->getId(): 0))
                            ->setParameter('locale', $income_locale)
                            ->orderBy('question.datetime', 'DESC')
                            ->getQuery()
                            ->setMaxResults(5)
                            ->getResult();

                        if ($user instanceof User){
                            foreach ($questions as $qid=>$question) {
                                if ($question->getSpams()->contains($user) || $user->getIgnoredQuestions()->contains($question) || !$this->geoFilterQuestion($user, $question) || $this->IAnsweredThisQuestion($user, $question)){
                                    unset($questions[$qid]);
                                }
                            }
                        }
                    }


                    if (count($questions)){
                        $categorized[$id]->questions = $questions;
                    } else {
                        unset($categorized[$id]);
                    }
                }
            }
        }

        if ($user instanceof User){
            foreach ($categorized as $id0=>$cat) {
                foreach ($cat->questions as $id=>$question){
                    $cat->questions[$id]->iAnswered = false;
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getUser()==$user){
                            $cat->questions[$id]->iAnswered = true;
                            if (!$category_id<0){
                                unset($cat->questions[$id]);
                            }
                        }
                    }
                    $categorized[$id0] = $cat;
                }
            }
        } else {
            foreach ($categorized as $id0=>$cat) {
                foreach ($cat->questions as $id=>$question){
                    $cat->questions[$id]->iAnswered = false;
                    $categorized[$id0] = $cat;
                }
            }
        }

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'По категориям',
             'categorized_questions' => $categorized,
             'curUser' => $this->getUser(),
             'form' => $form->createView(),
             'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
        );
    }

    public function rushAction(Request $request)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        if ($user instanceof User){
            $user->setLastRushView(new \DateTime());

            $em->persist($user);
            $em->flush();
        }

        $answer = new Answer();
        $form = $this->createForm(new NewAnswer(), $answer);
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('rush'));
            }
        }

        $income_locale = array('ru', 'en', 'pt');
        if ($user instanceof User){
            $my_settings = $user->getSettings();
            if ($my_settings instanceof UserSettings){
                if ($my_settings->getViewLocale()){
                    $income_locale = array();
                    foreach ($my_settings->getIncomeLocale() as $id=>$income_loc) {
                        if ($income_loc){
                            array_push($income_locale, $id);
                        }
                    }
                }
            }
        }

        $rush_id = 1;
        $categorized = array();
        $categorized[0] = $this->getDoctrine()->getRepository("CronCronBundle:Category")->find($rush_id);

        $rush = array();
        if (!empty($income_locale)){
            $rush = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                                    ->createQueryBuilder('question')
                                    ->where('question.category = :cid  AND question.status '.($user instanceof User ? '<>': '=').' :status AND question.isSpam = false AND question.user <> :user AND question.locale IN (:locale)')
                                    ->setParameter('cid', $rush_id)
                                    ->setParameter('status', '2')
                                    ->setParameter('user', ($user instanceof User ? $user->getId(): 0))
                                    ->setParameter('locale', $income_locale)
                                    ->orderBy('question.datetime', 'DESC')
                                    ->getQuery()
                                    ->getResult();
        }

        if ($user instanceof User){
            foreach ($rush as $id=>$question){
                $rush[$id]->iAnswered = false;
                if ($question->getSpams()->contains($user) || $user->getIgnoredQuestions()->contains($question) || !$this->geoFilterQuestion($user, $question) || $this->IAnsweredThisQuestion($user, $question)){
                    unset($rush[$id]);
                } else {
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getUser()==$user){
//                        $rush[$id]->iAnswered = true;
                            unset($rush[$id]);
                        }
                    }
                }
            }
        } else {
            foreach ($rush as $id=>$question){
                $rush[$id]->iAnswered = false;
            }
        }

        $categorized[0]->questions = $rush;

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'Срочные',
             'categorized_questions' => $categorized,
             'curUser' => $this->getUser(),
             'form' => $form->createView(),
             'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
        );
    }

    public function myAction(Request $request)
    {
        $user = $this->getUser();

        $my = array();
        if ($user instanceof User){
            $my = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findAllMyByUser($user);
        }

        return $this->render("CronCronBundle:Main:index.html.twig", array('title' => 'Мои вопросы',
                'userQuestions' => $my,
                'curUser' => $this->getUser(),
                'form' => null,
                'show_success_msg' => false,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function diskAction($file_hash)
    {
        $user = $this->getUser();

        if (!$file_hash){
            if (!$user instanceof User) {
                return $this->render("CronCronBundle:Main:disk.html.twig", array('title' => 'Кибердиск',
                                                                                 'curUser' => $user,
                                                                                 'isAuth' => 0,
                                                                                 'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
                );
            } else {
                $total_filesize = $this->getDoctrine()->getRepository("CronCronBundle:File")
                    ->createQueryBuilder('file')
                    ->select('SUM(file.filesize) as value')
                    ->where('file.user = :uid')
                    ->setParameter('uid', $user->getId())
                    ->groupBy('file.user')
                    ->getQuery()
                    ->getResult();

                $total_size = 0;
                $total_size_left = 52428800;
                if (!empty($total_filesize)){
                    $total_size = $total_filesize[0]['value'];
                    $total_size_left = 52428800 - $total_filesize[0]['value'];
                }

                $user_files = $this->getDoctrine()->getRepository("CronCronBundle:File")
                    ->createQueryBuilder('file')
                    ->where('file.user = :uid')
                    ->setParameter('uid', $user->getId())
                    ->orderBy('file.upload_date', 'DESC')
                    ->getQuery()
                    ->getResult();

                return $this->render("CronCronBundle:Main:disk.html.twig", array('title' => 'Кибердиск',
                        'total_filesize' => $this->convertFilesize($total_size),
                        'total_filesize_left' => $this->convertFilesize($total_size_left),
                        'user_files' => $user_files,
                        'curUser' => $user,
                        'isAuth' => 1,
                        'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
                );
            }

        } else {
            $file = $this->getDoctrine()->getRepository('CronCronBundle:File')->findOneBy(array('hash' => $file_hash));
            if (!$user instanceof User) {
                $isAuth = 0;
                return $this->render("CronCronBundle:Main:file.html.twig", array('title' => 'Скачать файл',
                        'file' => $file,
                        'curUser' => $user,
                        'isAuth' => $isAuth,
                        'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount)
                );
            } else {
                $isAuth = 1;
                return $this->redirect($file->getUrl());
            }
//            return header("Location: ".$file->getUrl());
            /**/
        }
    }

    public function settingsAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->redirect("/");
        }

        $request->setLocale($request->getSession()->get('_locale'));

        $categories = $this->getDoctrine()->getRepository("CronCronBundle:Category")->findAll();

        $user_settings = $this->getDoctrine()->getRepository("CronCronBundle:UserSettings")->findOneBy(array("user" => $user->getId()));

        //Settings converting (temporarily)
        $settings = array();

        $incomeCats = null;
        $incomeLocale = null;
        $viewCats = null;
        $viewLocale = null;
        $viewByTime = null;
        $sounds = null;

        if ($user_settings instanceof UserSettings)
            $incomeCats = $user_settings->getIncomeCats();
        if (!count($incomeCats) || !$incomeCats){
            foreach ($categories as $cat)
                $settings['income_cats'][$cat->getId()] = 'checked="checked"';
        } else {
            foreach ($categories as $cat)
                $settings['income_cats'][$cat->getId()] = '';
            foreach ($incomeCats as $id=>$cat)
                $settings['income_cats'][$id] = 'checked="checked"';
        }

        if ($user_settings instanceof UserSettings)
            $incomeLocale = $user_settings->getIncomeLocale();
        if (!count($incomeLocale) || !$incomeLocale){
            $settings['income_locale']['ru'] = 'checked="checked"';
            $settings['income_locale']['en'] = 'checked="checked"';
            $settings['income_locale']['pt'] = 'checked="checked"';
        } else {
            foreach ($incomeLocale as $id=>$locale){
                if ($locale)
                    $settings['income_locale'][$id] = 'checked="checked"';
                else
                    $settings['income_locale'][$id] = '';
            }
        }

        if ($user_settings instanceof UserSettings)
            $viewCats = $user_settings->getViewCats();
        if (!count($viewCats) || !$viewCats){
            foreach ($categories as $cat)
                $settings['view_cats'][$cat->getId()] = 'checked="checked"';
        } else {
            foreach ($categories as $cat)
                $settings['view_cats'][$cat->getId()] = '';
            foreach ($viewCats as $id=>$cat)
                $settings['view_cats'][$id] = 'checked="checked"';
        }

        if ($user_settings instanceof UserSettings)
            $viewLocale = $user_settings->getViewLocale();
        if (!count($viewLocale) || !$viewLocale){
            $settings['view_locale']['ru'] = 'checked="checked"';
            $settings['view_locale']['en'] = 'checked="checked"';
            $settings['view_locale']['pt'] = 'checked="checked"';
        } else {
            foreach ($viewLocale as $id=>$locale){
                if ($locale)
                    $settings['view_locale'][$id] = 'checked="checked"';
                else
                    $settings['view_locale'][$id] = '';
            }
        }

        if ($user_settings instanceof UserSettings)
            $viewByTime = $user_settings->getViewByTime();
        $settings['view_by_time']['day'] = '';
        $settings['view_by_time']['week'] = '';
        $settings['view_by_time']['month'] = '';
        $settings['view_by_time']['all'] = '';
        switch($viewByTime){
            case 'day':
                $settings['view_by_time']['day'] = 'checked="checked"';
                break;
            case 'week':
                $settings['view_by_time']['week'] = 'checked="checked"';
                break;
            case 'month':
                $settings['view_by_time']['month'] = 'checked="checked"';
                break;
            case 'all':
            default:
                $settings['view_by_time']['all'] = 'checked="checked"';
                break;
        }

        if ($user_settings instanceof UserSettings)
            $sounds = $user_settings->getSounds();
        if (!count($sounds) || !$sounds){
            $settings['sounds']['cats'] = 'checked="checked"';
            $settings['sounds']['rush'] = 'checked="checked"';
            $settings['sounds']['invite'] = 'checked="checked"';
            $settings['sounds']['chat'] = 'checked="checked"';
            $settings['sounds']['dialog'] = 'checked="checked"';
        } else {
            foreach ($sounds as $id=>$sound_setting) {
                if($sound_setting)
                    $settings['sounds'][$id] = 'checked="checked"';
                else
                    $settings['sounds'][$id] = '';
            }
        }


        return $this->render("CronCronBundle:Main:settings.html.twig", array('title' => 'Настройки',
                'categories' => $categories,
                'settings' => $settings,
                'curUser' => $user,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function articlesAction($category_id, $article_id)
    {
        $_locale = $this->container->get('session')->get('_locale');
        if (!$_locale)
            $_locale = 'ru_RU';

        $user = $this->getUser();

        if ($category_id>0 && $article_id>0){
            $cur_category = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->find($category_id);

            $cur_article = $this->getDoctrine()->getRepository("CronCronBundle:Article")->find($article_id);

            return $this->render("CronCronBundle:Articles:single_article.html.twig", array('title' => 'Статьи / '.$cur_category->getName().' / '.$cur_article->getHeader(),
                'category' => $cur_category,
                'article' => $cur_article,
                'curUser' => $user,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
            ));
        } elseif ($category_id>0){
            $cur_category = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->find($category_id);

            $articles = $this->getDoctrine()->getRepository("CronCronBundle:Article")->findBy(array("category"=>$category_id, "locale"=> $_locale));


            return $this->render("CronCronBundle:Articles:article_list.html.twig", array('title' => 'Статьи / '.$cur_category->getName(),
                'category' => $cur_category,
                'articles' => $articles,
                'curUser' => $user,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
            ));
        } else {
            $article_categories = array();
            $articles = $this->getDoctrine()->getRepository("CronCronBundle:Article")->findBy(array("locale"=> $_locale), array("id"=>"ASC"));
            foreach ($articles as $article) {
                $article_categories[$article->getCategory()->getId()] = $article->getCategory();
            }

//            $article_categories = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->findAll();

            return $this->render("CronCronBundle:Articles:article_categories.html.twig", array('title' => 'Статьи',
                'categories' => $article_categories,
                'curUser' => $user,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
            ));
        }

    }

    public function notesAction($location)
    {
        $user = $this->getUser();

        switch($location){
            case 'questions':
                $questions = $this->getDoctrine()->getRepository("CronCronBundle:NotesQuestion")->findBy(array("user"=>$user->getId()));
//                print_r($questions);
//                foreach ($questions as $id=>$question) {
//                    $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$question->getQuestion()->getId()));
//                    $questions[$id]->answers = $answers;
//                }
                if ($user instanceof User) {
                    foreach ($questions as $id => $question) {
                        $questions[$id]->iAnswered = false;
                        foreach ($question->getQuestion()->getAnswers() as $answer) {
                            if ($answer->getUser() == $user) {
                                $questions[$id]->iAnswered = true;
                            }
                        }
                    }
                } else {
                    foreach ($questions as $id => $question) {
                        $questions[$id]->iAnswered = false;
                    }
                }

                return $this->render("CronCronBundle:Notes:questions.html.twig", array('title' => 'Заметки / Статьи',
                    'questions' => $questions,
                    'curUser' => $user,
                    'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
                ));
                break;
            case 'articles':
                $articles = $this->getDoctrine()->getRepository("CronCronBundle:NotesArticle")->findBy(array("user"=>$user->getId()));
                return $this->render("CronCronBundle:Notes:articles.html.twig", array('title' => 'Заметки / Статьи',
                    'articles' => $articles,
                    'curUser' => $user,
                    'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
                ));
                break;
            default:break;
        }
    }

    public function creditsAction(Request $request)
    {
        $user = $this->getUser();


        return $this->render("CronCronBundle:Main:credits.html.twig", array('title' => 'Кредиты сайта',
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));

    }

    public function aboutAction()
    {
        return $this->render("CronCronBundle:Main:about.html.twig", array('title' => 'О нас',
            'curUser' => $this->getUser(),
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function rulesAction()
    {
        return $this->render("CronCronBundle:Main:rules.html.twig", array('title' => 'Правила',
            'curUser' => $this->getUser(),
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function advertAction()
    {
        return $this->render("CronCronBundle:Main:advert.html.twig", array('title' => 'Рекламодателям',
            'curUser' => $this->getUser(),
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function agreementAction()
    {
        return $this->render("CronCronBundle:Main:agreement.html.twig", array('title' => 'Пользовательское соглашение',
            'curUser' => $this->getUser(),
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function registerAction(Request $request)
    {
        $user = new User();
        /*if (!$user->getState()){
            $user->setState(null);
        }
        if (!$user->getCity()){
            $user->setCity(null);
        }*/
        $form = $this->createForm(new Registration($this->container->get('session')), $user);
        if ($request->isMethod('POST')) {

            $form->bind($request);

            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $forconf = $user->getPassword();
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);

//                if (!$user->getState()){
//                    $user->setState(new \Cron\CronBundle\Entity\State());
//                }

                $user->setCredits(10);

                $user->setLockedTill(new \DateTime("0000-00-00 00:00:00"));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $user = $this->getDoctrine()->getRepository("CronCronBundle:User")->findOneByUsername($user->getUsername());
                //acception
                $hash = md5($user->getId() + $user->getNick() + $user->getUsername());
                $mailer = $this->get('mailer');
                $translator = $this->get('translator');
                $message = \Swift_Message::newInstance(null, null, "text/html")
                    ->setSubject($translator->trans('Обратная связь'))
                    ->setFrom("aditus777@gmail.com")
                    ->setTo($user->getUsername())
                    ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' .
                    $translator->trans('Поздравляем Вас с успешной регистрацией!').'<br><br>' .
                    $translator->trans('Ваш логин').' ' . $user->getUsername() . '<br>' .
                    $translator->trans('Ваш пароль').' ' . $forconf . '<br>' .
                    $translator->trans('Перейдите по ссылке для подтверждения вашего e-mail адреса').':<br><a href="http://' . $_SERVER['HTTP_HOST'] . '/regconf?id=' . $user->getId() . '&hash=' . $hash . '">http://' . $_SERVER['HTTP_HOST'] . '/regconf?id=' . $user->getId() . '&hash=' . $hash . '</a><br>' .
                    '('.$translator->trans('если не можете нажать на нее, скопируйте ее в адресную строку Вашего браузера').')<br>' .
                    $translator->trans('Добро пожаловать на ADITUS.ru!').'<br><br>' .
                    $translator->trans('Данное сообщение было выслано автоматически. Это сообщение является служебным письмом, которое связано с вашей учётной записью на ADITUS. Если у вас есть вопросы или вам необходима помощь, вы можете обратиться в службу поддержки ADITUS.').'<br><br>' .
                    $translator->trans('Если Вы считаете, что данное сообщение послано Вам ошибочно, проигнорируйте его и все данные будут автоматически удалены.'));
                $mailer->send($message);

                return $this->redirect($this->generateUrl('index', array("reg_success"=>"1")));
            }
        }

        return $this->render("CronCronBundle:Main:register.html.twig", array('title' => 'Регистрация', 'curUser' => $this->getUser(), 'form' => $form->createView(), 'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount));
    }

    public function regconfAction(Request $request)
    {
        $success = false;

        if ($request->isMethod("GET")) {
            $id = $request->get("id");
            $hash = $request->get("hash");
            $user = $this->getDoctrine()->getRepository("CronCronBundle:User")->findOneById($id);
            if (!$user instanceof User)
                $this->render("CronCronBundle:Main:registration_confirmation.html.twig", array('title' => 'Подтверждение регистрации', 'curUser' => $this->getUser(), 'success' => $success));
            if (md5($user->getId() + $user->getNick() + $user->getUsername()) == $hash)
            {
                $user->setIsActive(true);

                $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
                $this->container->get('security.context')->setToken($token);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $success = true;

                return $this->redirect("/", 302);
            }
        }

        return $this->render("CronCronBundle:Main:registration_confirmation.html.twig", array('title' => 'Подтверждение регистрации', 'curUser' => $this->getUser(), 'success' => $success, 'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount));
    }

    public function localeAction(Request $request, $locale)
    {
        $request->getSession()->set('_locale', $locale);

        return $this->redirect($request->headers->get('referer'));
    }

    public function convertFilesize($input_filesize)
    {
        /*$sizeStr = array("байт", "Кб", "МБ", "ГБ");
        $index = log($input_filesize, 1024);

        return $sizeStr[floor($index)];*/

        $filesize = $input_filesize;
        if($filesize > 1024){
            $filesize = ($filesize/1024);
            if($filesize > 1024){
                $filesize = ($filesize/1024);
                if($filesize > 1024){
                    $filesize = ($filesize/1024);
                    $filesize = round($filesize, 1);
                    return $filesize." ГБ";
                } else {
                    $filesize = round($filesize, 1);
                    return $filesize." MБ";
                }
            } else {
                $filesize = round($filesize, 1);
                return $filesize." Кб";
            }
        } else {
            $filesize = round($filesize, 1);
            return $filesize." байт";
        }
    }
}
