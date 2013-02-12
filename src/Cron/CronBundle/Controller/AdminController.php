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
            'form' => $form->createView()
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
            'curUser' => $user
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
        /*return $this->render("CronCronBundle:Admin:articles.html.twig", array('title' => 'Статьи',
            'articles' => $articles,
            'curUser' => $user
        ));*/
        return new Response("SUCCESS");

    }

}
