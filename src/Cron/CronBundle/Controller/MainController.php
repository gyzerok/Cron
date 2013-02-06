<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Form\NewQuestion;
use Cron\CronBundle\Form\NewAnswer;
use Cron\CronBundle\Form\Registration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function indexAction(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $question = new Question();
        $form = $this->createForm(new NewQuestion(), $question);

        if ($request->isMethod('POST'))
        {
            $form->bind($request);

            if ($form->isValid())
            {
                $user = $this->getUser();
                if (!$user instanceof User)
                    $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');

                $question->setUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }
        }

        $user = $this->getUser();
        $userQuestions = null;
        if ($user instanceof User)
            $userQuestions = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                                                 ->createQueryBuilder('question')
                                                 ->where('question.user = :uid  AND question.status <> :status')
                                                 ->setParameter('status', '2')
                                                 ->setParameter('uid', $user->getId())
                                                 ->getQuery()
                                                 ->getResult();

        return $this->render("CronCronBundle:Main:index.html.twig", array('title' => 'Главная',
                                                                          'curUser' => $this->getUser(),
                                                                          'userQuestions' => $userQuestions,
                                                                          'form' => $form->createView())
        );

    }

    public function categoryAction(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $answer = new Answer();
        $form = $this->createForm(new NewAnswer(), $answer);
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('category'));
            }
        }

        $categorized = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                                           ->createQueryBuilder('question')
                                           ->innerJoin('question.user', 'user')
                                           ->where('question.category > :cid  AND question.status <> :status')
                                           ->setParameter('cid', '1')
                                           ->setParameter('status', '2')
                                           ->getQuery()
                                           ->getResult();

        foreach ($categorized as $id=>$question) {
            $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$question->getId()), array("pubDate"=>"ASC"));
            $categorized[$id]->answers = $answers;
        }

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'По категориям',
                                                                             'questions' => $categorized,
                                                                             'curUser' => $this->getUser(),
                                                                             'form' => $form->createView())
        );
    }

    public function rushAction(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $answer = new Answer();
        $form = $this->createForm(new NewAnswer(), $answer);
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('rush'));
            }
        }

        $rush = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                                    ->createQueryBuilder('question')
                                    ->innerJoin('question.user', 'user')
                                    ->where('question.category = :cid  AND question.status <> :status')
                                    ->setParameter('cid', '1')
                                    ->setParameter('status', '2')
                                    ->getQuery()
                                    ->getResult();

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'Срочные',
                                                                             'questions' => $rush,
                                                                             'curUser' => $this->getUser(),
                                                                             'form' => $form->createView())
        );
    }

    public function diskAction($file_hash)
    {
        //$request->setLocale($request->getSession()->get('_locale'));

        $user = $this->getUser();

        if (!$file_hash){
            if (!$user instanceof User) {
                return $this->render("CronCronBundle:Main:disk.html.twig", array('title' => 'Кибердиск',
                                                                                 'curUser' => $user,
                                                                                 'isAuth' => 0)
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
                        'isAuth' => 1)
                );
            }

        } else {
            $file = $this->getDoctrine()->getRepository('CronCronBundle:File')->findOneBy(array('hash' => $file_hash));
            if (!$user instanceof User) {
                $isAuth = 0;
                return $this->render("CronCronBundle:Main:file.html.twig", array('title' => 'Скачать файл',
                        'file' => $file,
                        'curUser' => $user,
                        'isAuth' => $isAuth)
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
                'curUser' => $user
        ));
    }

    public function registerAction(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

        $user = new User();
        $form = $this->createForm(new Registration(), $user);
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $forconf = $user->getPassword();
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $user = $this->getDoctrine()->getRepository("CronCronBundle:User")->findOneByUsername($user->getUsername());
                //acception
                $hash = md5($user->getId() + $user->getNick() + $user->getUsername());
                $mailer = $this->get('mailer');
                $message = \Swift_Message::newInstance(null, null, "text/html")
                    ->setSubject('Обратная связь')
                    ->setFrom("aditus777@gmail.com")
                    ->setTo($user->getUsername())
                    ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' .
                    'Поздравляем Вас с успешной регистрацией!<br>' .
                    'Ваш логин ' . $user->getUsername() . '<br>' .
                    'Ваш пароль ' . $forconf . '<br>' .
                    'Перейдите по ссылке для подтверждения вашего e-mail адреса:<br><a href="http://' . $_SERVER['HTTP_HOST'] . '/regconf?id=' . $user->getId() . '&hash=' . $hash . '">http://' . $_SERVER['HTTP_HOST'] . '/regconf?id=' . $user->getId() . '&hash=' . $hash . '</a><br>' .
                    '(если не можете нажать на нее, скопируйте ее в адресную строку Вашего браузера)<br>' .
                    'Добро пожаловать на ADITUS.ru!<br><br>' .
                    'Данное сообщение было выслано автоматически. Это сообщение является служебным письмом, которое связано с вашей учётной записью на ADITUS. Если у вас есть вопросы или вам необходима помощь, вы можете обратиться в службу поддержки ADITUS.<br><br>' .
                    'Если Вы считаете, что данное сообщение послано Вам ошибочно, проигнорируйте его и все данные будут автоматически удалены.');
                $mailer->send($message);

                return $this->redirect($this->generateUrl('index'));
            }
        }

        return $this->render("CronCronBundle:Main:register.html.twig", array('title' => 'Регистрация', 'curUser' => $this->getUser(), 'form' => $form->createView()));
    }

    public function regconfAction(Request $request)
    {
        $request->setLocale($request->getSession()->get('_locale'));

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

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $success = true;
            }
        }

        return $this->render("CronCronBundle:Main:registration_confirmation.html.twig", array('title' => 'Подтверждение регистрации', 'curUser' => $this->getUser(), 'success' => $success));
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
