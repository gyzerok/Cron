<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Form\NewQuestion;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        $question = new Question();

        $form = $this->createForm(new NewQuestion(), $question);
        return $this->render("CronCronBundle:Main:index_temp.html.twig", array('title' => 'Главная',
                                                                               'form' => $form->createView())
                                                                               );
    }
}
