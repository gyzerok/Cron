<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Form\NewQuestion;
use Cron\CronBundle\Form\NewAnswer;
use Cron\CronBundle\Form\Registration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    public function indexAction(Request $request)
    {
        $question = new Question();
        $form = $this->createForm(new NewQuestion(), $question);

        if($request->isMethod('POST'))
        {
            $form->bind($request);

            if($form->isValid())
            {
                $state_id = $request->get('question');//['state'];
                $state = $this->getDoctrine()->getRepository('CronCronBundle:State')->findOneById($state_id['state']);
                $question->setState($state);
                $city_id = $request->get('question');//['city'];
                $city = $this->getDoctrine()->getRepository('CronCronBundle:City')->findOneById($city_id['city']);
                $question->setCity($city);

                $question->setStatus(true);
                //TODO Сделать нормального юзера
                $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
                $question->setUser($user); // заглушка

                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }
        }

        return $this->render("CronCronBundle:Main:index.html.twig", array('title' => 'Главная',
                                                                          'curUser' => $this->getUser(),
                                                                          'form' => $form->createView())
                                                                          );

    }

    public function categoryAction(Request $request)
    {
        $answer = new Answer();
        $form = $this->createForm(new NewAnswer(), $answer);
        if($request->isMethod('POST'))
        {
            $form->bind($request);

            if($form->isValid())
            {
                return $this->redirect($this->generateUrl('category'));
            }
        }

        $categorized = $this->getDoctrine()->getRepository("CronCronBundle:Question")
                                           ->createQueryBuilder('question')
                                           ->innerJoin('question.user', 'user')
                                           ->where('question.category > :cid')
                                           ->setParameter('cid', '1')
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
        if($request->isMethod('POST'))
        {
            $form->bind($request);

            if($form->isValid())
            {
                return $this->redirect($this->generateUrl('rush'));
            }
        }

        $rush = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findByCategory(1);

        return $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'Срочные',
                                                                             'questions' => $rush,
                                                                             'curUser' => $this->getUser(),
                                                                             'form' => $form->createView())
                                                                             );
    }

    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new Registration(), $user);
        if($request->isMethod('POST'))
        {
            $form->bind($request);

            if($form->isValid())
            {
                $state_id = $request->get('register');//['state'];
                $state = $this->getDoctrine()->getRepository('CronCronBundle:State')->findOneById($state_id['state']);
                $user->setState($state);
                $city_id = $request->get('register');//['city'];
                $city = $this->getDoctrine()->getRepository('CronCronBundle:City')->findOneById($city_id['city']);
                $user->setCity($city);

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }
        }

        return $this->render("CronCronBundle:Main:register.html.twig", array('title' => 'Регистрация', 'curUser' => $this->getUser(), 'form' => $form->createView()));
    }
}
