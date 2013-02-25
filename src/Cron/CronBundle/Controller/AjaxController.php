<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cron\CronBundle\Entity\Question;
use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\UserSettings;
use Cron\CronBundle\Entity\UserLink;

class AjaxController extends Controller
{
    public function getStatesAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $countryId = $request->get("country_id");

            $states = $this->getDoctrine()->getRepository('CronCronBundle:State')->findByCountry($countryId);

            $html = '';
            foreach($states as $state)
                $html = $html . sprintf('<option value="%d">%s</option>', $state->getId(), $state->getName());

            return new Response($html);
        }
    }

    public function getCitiesAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $stateId = $request->get("state_id");

            $cities = $this->getDoctrine()->getRepository('CronCronBundle:City')->findByState($stateId);

            $html = '';
            foreach($cities as $city)
                $html = $html . sprintf('<option value="%d">%s</option>', $city->getId(), $city->getName());

            return new Response($html);
        }
    }

    public function getUpdateAction(Request $request)
    {
        if($request->isMethod('POST'))
        {
            $lastTime = $request->get("last_time");
            $lastTime = date('Y-m-d H:i:s', $lastTime);

            $questionRepo = $this->getDoctrine()->getRepository('CronCronBundle:Question');
            $catQuery = $questionRepo->createQueryBuilder('question')
                                     ->innerJoin('question.user', 'user')
                                     ->where('question.category > :cid AND question.datetime > :lastTime AND question.status <> :status AND question.isSpam = false')
                                     ->setParameter('cid', '1')
                                     ->setParameter('lastTime', $lastTime)
                                     ->setParameter('status', '2')
                                     ->getQuery();
            $categorized = $catQuery->getResult();

            $rushQuery = $questionRepo->createQueryBuilder('question')
                                      ->innerJoin('question.user', 'user')
                                      ->where('question.category = :cid AND question.datetime > :lastTime AND question.status <> :status AND question.isSpam = false')
                                      ->setParameter('cid', '1')
                                      ->setParameter('lastTime', $lastTime)
                                      ->setParameter('status', '2')
                                      ->getQuery();
            $rush = $rushQuery->getResult();
            /*$answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')
                                           ->createQueryBuilder('answer')
                                           ->innerJoin('answer.user', 'user')
                                           ->where('answer.status <> :status')
                                           ->setParameter('status', '0')
                                           ->getQuery()->getResult();*/

            $json = '{';

            $json = $json . '"categorized":{' . $this->questionsToJSON($categorized) . '},';
            $json = $json . '"rush":{' . $this->questionsToJSON($rush) . '},';
            //$json = $json . '"answers":{' . $this->questionsToJSON()
            $json = $json . '"last_time":' . time();
            $json = $json . '}';

            return new Response($json);
        }

        return new Response('error');
    }

    public function delQuestionAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $questionId = $request->get("question_id");
            $full = $request->get("full");

            $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
            if (!$question instanceof Question)
                return new Response('Fail');

            if ($full)
                $question->setStatus(2);
            else
                $question->setStatus(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return new Response('SUCCESS');
        }

        return new Response('Fail');
    }

    public function likeItemAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($questionId = $request->get('question_id'))
            {
                $question = new Question();
                $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
                if (!$question instanceof Question)
                    return new Response('Fail');

                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');
                    //$user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');

                if ($question->getLikes()->contains($user))
                    return new Response('Fail');

                $question->addLike($user);

                $creator = $question->getUser();
                $creator->incCredits();

                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->persist($creator);
                $em->flush();

                return new Response('Success');
            }
            elseif ($answerId = $request->get('answer_id'))
            {
                $answer = new Answer();
                $answer = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findOneById($answerId);
                if (!$answer instanceof Question)
                    return new Response('Fail');

                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');

                if ($answer->getLikes()->contains($user))
                    return new Response('Fail');

                $answer->addLike($user);

                $creator = $answer->getUser();
                $creator->incCredits();

                $em = $this->getDoctrine()->getManager();
                $em->persist($answer);
                $em->persist($creator);
                $em->flush();

                return new Response('Success');
            }
        }

        return new Response('Fail');
    }

    public function spamQuestionAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($questionId = $request->get('question_id'))
            {
                $em = $this->getDoctrine()->getManager();
                $question = new Question();
                $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
                if (!$question instanceof Question)
                    return new Response('Fail');

                $user = new User();
                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');

                if ($question->getSpams()->contains($user))
                    return new Response('Fail');

                $question->addSpam($user);

                if ($question->getBoundary() >= 50)
                    $spamBoundary = 10;
                else
                    $spamBoundary = 5;
                if ($question->getSpams()->count() >= $spamBoundary)
                {
                    $question->setIsSpam(true);

                    $user->setSpamActivity($user->getSpamActivity() + 1);
                    $banDate = new \DateTime();
                    $banDate->add(new \DateInterval('P100Y'));
                    if ($user->getSpamActivity() >= 5)
                        $user->setLockedTill($banDate);
                    $em->persist($user);
                }

                $em->persist($question);
                $em->flush();

                return new Response('Success');
            }
        }

        return new Response('Fail');
    }

    public function spamAnswerAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($answerId = $request->get('answer_id'))
            {
                $em = $this->getDoctrine()->getManager();
                $answer = new Answer();
                $answer = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findOneById($answerId);
                if (!$answer instanceof Answer)
                    return new Response('Fail');

                $user = new User();
                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');

                if ($answer->getSpams()->contains($user))
                    return new Response('Fail');

                $answer->addSpam($user);

                /*if ($question->getBoundary() >= 50)
                    $spamBoundary = 10;
                else
                    $spamBoundary = 5;*/
                if ($answer->getSpams()->count() >= 5)
                {
                    $answer->setIsSpam(true);

                    $user->setSpamActivity($user->getSpamActivity() + 1);
                    $banDate = new \DateTime();
                    $banDate->add(new \DateInterval('P100Y'));
                    if ($user->getSpamActivity() >= 5)
                        $user->setLockedTill($banDate);
                    $em->persist($user);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($answer);
                $em->flush();

                return new Response('Success');
            }
        }

        return new Response('Fail');
    }

    public function postAnswerAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $questionId = $request->get("id");
            $answerText = $request->get("text");

            $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
            if (!$question instanceof \Cron\CronBundle\Entity\Question)
                return new Response('Fail');

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setText($answerText);
            $user = $this->getUser();
            if (!$user instanceof User)
                $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
            $answer->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            if ($question->getPrivate()){
                $answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findby(array("id"=>$answer->getId()));
            } else {
                $answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findby(array("question"=>$question->getId()), array("pubDate"=>"ASC"));
            }

            $html = '';
            foreach ($answers as $ans) {
                $html .= '<div class="singleAnswer" data-user="'.$ans->getUser()->getId().'">
					<div class="userName">'.$ans->getUser()->getNick().'</div>
					<div class="answerDate">'.$ans->getPubDate()->format("Y-m-d H:i:s").'</div>
					<div style="clear: both;"></div>
					<div class="questionText">
						'.$ans->getText().'
						<div class="socialIcons">
							<div class="spamButton"></div>
							<div class="likeButton"></div>
							<div class="arrowButton inviteUser"></div>
							<div class="letterButton sendMessage"></div>
						</div>
					</div>
				</div>';
            }

            return new Response($html);
        }
    }

    public function questionsToJSON(array $questions)
    {
        $json = '';
        foreach($questions as $question)
            $json = $json . sprintf('"%d":{ "text":"%s", "user":"%s", "date":"%s"},', $question->getId(), $question->getText(), $question->getUser(), $question->getDatetime()->format('H:i:s d.m.Y'));

        $json = substr($json, 0, -1);
        return $json;
    }

    public function uploadFileAction(Request $request)
    {
        $user = $this->getUser();
        if ($request->isMethod('POST') && ($user instanceof User)){
            $files_dir = $_SERVER['DOCUMENT_ROOT'].'/files/'.$user->getId().'/';
            if (!is_dir($files_dir))
                mkdir($files_dir);
            $file_name = iconv("utf-8", "cp1251", $_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $files_dir.$file_name);

            $total_filesize = $this->getDoctrine()->getRepository("CronCronBundle:File")
                ->createQueryBuilder('file')
                ->select('SUM(file.filesize) as value')
                ->where('file.user = :uid')
                ->setParameter('uid', $user->getId())
                ->groupBy('file.user')
                ->getQuery()
                ->getResult();

            $total_size_left = 52428800;
            if (!empty($total_filesize)){
                $total_size_left = 52428800 - $total_filesize[0]['value'];
            }
            if (filesize($files_dir.$file_name) <= $total_size_left){
                $file = new File();
                $file->setFilename($_FILES['file']['name']);
                $file->setUrl('http://'.$_SERVER['HTTP_HOST'].'/files/'.$user->getId().'/'.$_FILES['file']['name']);
                $file->setHash(substr(md5(file_get_contents($files_dir.$file_name)),0,8));
                $file->setFilesize(filesize($files_dir.$file_name));
                $file->setUploadDate(new \DateTime());
                $user = $this->getUser();
                if (!$user instanceof User)
                    $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
                $file->setUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($file);
                $em->flush();
                return new Response($file->getId());
            } else {
                @unlink($files_dir.$file_name);
                return new Response('FAIL', 403);
            }
        } else return new Response('FAIL', 403);
    }

    public function deleteFileAction(Request $request)
    {
        $user = $this->getUser();
        $file = $this->getDoctrine()->getRepository('CronCronBundle:File')->findOneBy(array('id' => $request->get('file_id'), 'user' => $user->getId()));
        //$file = $this->getDoctrine()->getRepository('CronCronBundle:File')->findById('1');
        $em = $this->getDoctrine()->getManager();
        $em->remove($file);
        $em->flush();

        $files_dir = $_SERVER['DOCUMENT_ROOT'].'/files/'.$user->getId().'/';
        @unlink($files_dir.iconv("utf-8", "cp1251", $file->getFilename()));
        return new Response('SUCCESS');
    }

    public function updateFilesizeAction(Request $request)
    {
        $user = $this->getUser();
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

        return new Response($this->convertFilesize($total_size).' / '.$this->convertFilesize($total_size_left));
    }

    public function getLastFileAction(Request $request)
    {
        $user = $this->getUser();
        $last_file = $this->getDoctrine()->getRepository("CronCronBundle:File")->findOneBy(array('id' => $request->get('file_id'), 'user' => $user->getId()));
        $html = '<div class="my-file" fileid="'.$last_file->getId().'" filepath="'.'http://'.$_SERVER['HTTP_HOST'].'/disk/'.$last_file->getHash().'">
                <table width="100%">
                    <tr>
                        <td><div class="my-file-name">'.$last_file->getFilename().'</div></td>
                        <td width="90"><div class="my-file-size">'.$last_file->getFilesize().'</div></td>
                        <td width="210"><input type="button" class="delete-my-file" value="удалить"><input type="button" class="download-my-file" value="загрузить"></td>
                    </tr>
                </table>
            </div>';
        return new Response($html);
    }

    public function saveSettingsAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail");
        }

        $user_settings = $this->getDoctrine()->getRepository("CronCronBundle:UserSettings")->findOneBy(array('user' => $user->getId()));
        if (!$user_settings instanceof UserSettings){
            $user_settings = new UserSettings();
            $user_settings->setUser($user);
            $user_settings->setIncomeCats(array());
            $user_settings->setViewCats(array());
        }

        switch($request->get("group")){
            case "income":
                $incomeCats = array();
                foreach ($request->get("cat") as $id=>$cat) {
                    $incomeCats[$id] = (bool)$cat;
                }
                $user_settings->setIncomeCats($incomeCats);
                $user_settings->setIncomeLocale(array("ru"=>(bool)$request->get("ru"), "en"=>(bool)$request->get("en"), "pt"=>(bool)$request->get("pt")));
                break;

            case "view":
                $viewCats = array();
                foreach ($request->get("cat") as $id=>$cat) {
                    $viewCats[$id] = (bool)$cat;
                }
                $user_settings->setViewCats($viewCats);
                $user_settings->setViewLocale(array("ru"=>(bool)$request->get("ru"), "en"=>(bool)$request->get("en"), "pt"=>(bool)$request->get("pt")));
                $user_settings->setViewByTime($request->get("by_time"));
                break;

            case "sound":
                $user_settings->setSounds(array("cats"=>(bool)$request->get("cats"), "rush"=>(bool)$request->get("rush"), "invite"=>(bool)$request->get("invite"), "chat"=>(bool)$request->get("chat"), "dialog"=>(bool)$request->get("dialog")));
                break;

            default:break;
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user_settings);
        $em->flush();

        return new Response("SUCCESS");
    }

    public function newUserLinkAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $link = new UserLink();
        $link->setUser($user)
            ->setTitle($request->get('title'))
            ->setUrl($request->get('url'))
            ->setDatetime(new \DateTime());


        $em = $this->getDoctrine()->getManager();
        $em->persist($link);
        $em->flush();

        $html = '<li><a href="'.$request->get('url').'" target="_blank">'.$request->get('title').'</a></li>';

        return new Response($html);
    }

    public function getUserLinksAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $links = $this->getDoctrine()->getRepository("CronCronBundle:UserLink")->findBy(array('user' => $user->getId()));

        $html = '';
        foreach ($links as $link) {
            $html .= '<li><a href="'.$link->getUrl().'" target="_blank">'.$link->getTitle().'</a></li>';
        }

        return new Response($html);
    }

    public function repostQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($request->get('question'));

        $notes_question = new \Cron\CronBundle\Entity\NotesQuestion();
        $notes_question->setUser($user)
            ->setQuestion($question)
            ->setDatetime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($notes_question);
        $em->flush();

        return new Response("SUCCESS");
    }

    public function bookmarkArticleAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $article = $this->getDoctrine()->getRepository("CronCronBundle:Article")->find($request->get('article'));

        $notes_article = new \Cron\CronBundle\Entity\NotesArticle();
        $notes_article->setUser($user)
            ->setArticle($article)
            ->setDatetime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($notes_article);
        $em->flush();

        return new Response("SUCCESS");
    }

    public function unbookmarkArticleAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $article = $this->getDoctrine()->getRepository("CronCronBundle:NotesArticle")->find($request->get('article'));

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return new Response("SUCCESS");
    }

    public function loadNotepadAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $notepad = $this->getDoctrine()->getRepository("CronCronBundle:Notepad")->findOneBy(array("user"=>$user->getId()));

        if (!$notepad instanceof \Cron\CronBundle\Entity\Notepad){
            $notepad = new \Cron\CronBundle\Entity\Notepad();
            $notepad->setUser($user);
            $notepad->setText('');
        }
        $notepad->setDatetime(new \DateTime());
//        $notepad->setText('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($notepad);
        $em->flush();

        return new Response($notepad->getText());
    }

    public function updateNotepadAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", "403");
        }

        $notepad = $this->getDoctrine()->getRepository("CronCronBundle:Notepad")->findOneBy(array("user"=>$user->getId()));

        if (!$notepad instanceof \Cron\CronBundle\Entity\Notepad){
            $notepad = new \Cron\CronBundle\Entity\Notepad();
            $notepad->setUser($user);
        }
        $notepad->setDatetime(new \DateTime());
        $notepad->setText($request->get('text'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($notepad);
        $em->flush();

        return new Response("SUCCESS");
    }

    public function convertFilesize($input_filesize)
    {
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
