<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Form\NewQuestion;

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
                $question->setStatus(true);
                $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
                $question->setUser($user); // заглушка
                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }
        }

        return $this->render("CronCronBundle:Main:index_temp.html.twig", array('title' => 'Главная',
                                                                               'form'  => $form->createView())
                                                                               );

    }

    public function categoryAction()
    {
        //TODO Добавить реализацию
        $this->render("CronCronBundle:Main:category.html.twig", array('title' => 'По категориям'));
    }

    public function rushAction()
    {
        //TODO Подумать над формированием запроса
        $rush = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findByCategory(0);
        $this->render("CronCronBundle:Main:rush.html.twig", array('title' => 'Срочные', 'rush' => $rush));
    }
}
