<?php

namespace Cron\CronBundle\Controller;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\Article;
use Cron\CronBundle\Entity\ArticleCategory;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Form\NewArticle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function newarticleAction(Request $request)
    {
        $user = $this->getUser();

        if ($request->isMethod('post')){

            $em = $this->getDoctrine()->getManager();

            $post_data = $request->get('article');

            $category = $this->getDoctrine()->getRepository("CronCronBundle:ArticleCategory")->find($post_data['category']);

            $imgs = array();

            $article = new Article();
            $article->setHeader($post_data['header'])
                ->setLocale($post_data['locale'])
//                ->setImgs($imgs)
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

            $article->setImgs($imgs);
            $em->persist($article);
            $em->flush();
        }
        $form = $this->createForm(new NewArticle());

        return $this->render("CronCronBundle:Admin:newarticle.html.twig", array('title' => 'Новая статья',
            'curUser' => $this->getUser(),
            'form' => $form->createView()
        ));

    }

}
