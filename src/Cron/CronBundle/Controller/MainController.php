<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Form\NewQuestion;
use Cron\CronBundle\Form\NewAnswer;
use Cron\CronBundle\Form\Registration;
use Cron\CronBundle\Form\New1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function indexAction(Request $request)
    {
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

    //test
    public function newAction(Request $request)
    {
        $question = new Question();
        $form = $this->createForm(new New1(), $question);

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

                return $this->redirect($this->generateUrl('new'));
            }
        }

        return $this->render("CronCronBundle:Main:index_temp.html.twig", array('title' => 'Главная',
            'curUser' => $this->getUser(),
            'form' => $form->createView())
        );
    }

    public function categoryAction(Request $request)
    {
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

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'По категориям',
                'questions' => $categorized,
                'curUser' => $this->getUser(),
                'form' => $form->createView())
        );
    }

    public function rushAction(Request $request)
    {
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

    public function registerAction(Request $request)
    {
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
