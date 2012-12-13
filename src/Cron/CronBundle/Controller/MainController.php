<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Form\NewQuestion;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function indexAction()
    {
        $question = new Question();

        $form = $this->createForm(new NewQuestion(), $question);
        return $this->render("CronCronBundle:Questions:new.html.twig", array('form' => $form->createView()));// new Response("<h1>Check this!</h1>");
    }
}
