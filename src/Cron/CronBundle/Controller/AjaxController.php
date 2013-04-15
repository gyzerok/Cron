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
use Cron\CronBundle\Entity\Chat;

use Cron\CronBundle\Model\SpamEngine;

class AjaxController extends AbstractController
{
    public function getStatesAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $countryId = $request->get("country_id");

            $states = $this->getDoctrine()->getRepository('CronCronBundle:State')->findByCountry($countryId);

            $html = '';
            foreach($states as $state)
                $html = $html . sprintf('<option value="%d">%s</option>', $state->getId(), $state->getName($this->locale));

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
                $html = $html . sprintf('<option value="%d">%s</option>', $city->getId(), $city->getName($this->locale));

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
                $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->find($questionId);
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
                $answer = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->find($answerId);
                if (!$answer instanceof Answer)
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

    public function spamItemAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($qid = $request->get('question_id'))
            {
                $spamEngine = new SpamEngine($this->getDoctrine(), $this->get('mailer'), $this->get('translator'));
                $spamEngine->markQuestionAsSpam($this->getUser(), $qid);

                return new Response('Success');
            } elseif ($aid = $request->get('answer_id'))
            {
                $user = $this->getUser();
                if (!$user instanceof User){
                    $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
                }
                $spamEngine = new SpamEngine($this->getDoctrine(), $this->get('mailer'), $this->get('translator'));
                $spamEngine->markAnswerAsSpam($user, $aid);

                return new Response('Success');
            }
        }

        throw new \Exception('Unknown error');
    }
    public function spamQuestionAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($qid = $request->get('question_id'))
            {
                $spamEngine = new SpamEngine($this->getDoctrine(), $this->get('mailer'), $this->get('translator'));
                $spamEngine->markQuestionAsSpam($this->getUser(), $qid);

                return new Response('Success');
            }
        }

        throw new \Exception('Unknown error');
    }

    public function spamAnswerAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            if ($aid = $request->get('answer_id'))
            {
                $spamEngine = new SpamEngine($this->getDoctrine(), $this->get('mailer'), $this->get('translator'));
                $spamEngine->markAnswerAsSpam($this->getUser(), $aid);

                return new Response('Success');
            }
        }

        throw new \Exception('Unknown error');
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
            $answer->setIsSpam(0);
            $user = $this->getUser();
            if (!$user instanceof User)
                $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
            $answer->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            $html = '';
            if ($question->getPrivate()){
//                $answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findby(array("id"=>$answer->getId()));
                $html = '<span class="spamMessage">'.$this->get('translator')->trans('вопрос является закрытым, вы не можете просматривать ответы других пользователей').'</span>';
            } else {
                $answers = $this->getDoctrine()->getRepository('CronCronBundle:Answer')->findby(array("question"=>$question->getId()), array("pubDate"=>"ASC"));
                foreach ($answers as $ans) {
                    $html .= '<div class="singleAnswer" data-user="'.$ans->getUser()->getId().'">
					<div class="userName">'.$ans->getUser()->getNick().'</div>
					<div class="answerDate">'.$ans->getPubDate()->format("d.m.Y H:i").'</div>
					<div style="clear: both;"></div>
					<div class="questionText">
						'.$ans->getText().'
						<div class="socialIcons">
						</div>
					</div>
				</div>';
                }
            }
            return new Response($html);
        }
    }

    public function checkCashAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("", 200);
        }

        $need_cash = $request->get('need_cash');

        if ($user->getCredits()<$need_cash){
            return new Response("", 403);
        }

        return new Response("OK");
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
//            $file_name = iconv("utf-8", "cp1251", $_FILES['file']['name']);
            $file_name = $_FILES['file']['name'];
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
                        <td width="210"><input type="button" class="delete-my-file" value="'.$this->get('translator')->trans('удалить').'"><input type="button" class="download-my-file" value="'.$this->get('translator')->trans('загрузить').'"></td>
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

        $url = $request->get('url');
        if (substr($url,0,7)!='http://'){
            $url = "http://".$url;
        }

        $link->setUser($user)
            ->setTitle($request->get('title'))
            ->setUrl($url)
            ->setDatetime(new \DateTime());


        $em = $this->getDoctrine()->getManager();
        $em->persist($link);
        $em->flush();

        $html = '<li data-id="'.$link->getId().'"><a href="'.$url.'" target="_blank">'.$request->get('title').'</a><a title="удалить ссылку" class="delete-link"></a></li>';

        return new Response($html);
    }

//    public function getUserLinksAction(Request $request)
//    {
//        $user = $this->getUser();
//        if (!$user instanceof User){
//            return new Response("Fail", "403");
//        }
//
//        $links = $this->getDoctrine()->getRepository("CronCronBundle:UserLink")->findBy(array('user' => $user->getId()));
//
//        $html = '';
//        foreach ($links as $link) {
//            $html .= '<li><a href="'.$link->getUrl().'" target="_blank">'.$link->getTitle().'</a></li>';
//        }
//
//        return new Response($html);
//    }

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

//    public function loadNotepadAction(Request $request)
//    {
//        $user = $this->getUser();
//        if (!$user instanceof User){
//            return new Response("Fail", "403");
//        }
//
//        $notepad = $this->getDoctrine()->getRepository("CronCronBundle:Notepad")->findOneBy(array("user"=>$user->getId()));
//
//        if (!$notepad instanceof \Cron\CronBundle\Entity\Notepad){
//            $notepad = new \Cron\CronBundle\Entity\Notepad();
//            $notepad->setUser($user);
//            $notepad->setText('');
//        }
//        $notepad->setDatetime(new \DateTime());
////        $notepad->setText('');
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($notepad);
//        $em->flush();
//
//        return new Response($notepad->getText());
//    }

    public function openNotepadAction(Request $request)
    {
        $request->getSession()->set('_notepad_opened', 1);
        return new Response("SUCCESS");
    }

    public function closeNotepadAction(Request $request)
    {
        $request->getSession()->remove('_notepad_opened');
        return new Response("SUCCESS");
    }

    public function openChatAction(Request $request)
    {
        $request->getSession()->set('_chat_opened', 1);
        return new Response("SUCCESS");
    }

    public function closeChatAction(Request $request)
    {
        $request->getSession()->remove('_chat_opened');
        return new Response("SUCCESS");
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

    public function updateQuestionsAction(Request $request)
    {
        $guest = false;
        $user = $this->getUser();
        if (!$user instanceof User){
            $guest = true;
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }
//        $lastTime = $request->get("questions_last_update");

        $em = $this->getDoctrine()->getManager();

        $questionRepo = $this->getDoctrine()->getRepository('CronCronBundle:Question');

        $data = array();

        if (!$guest){
            $my_settings = $user->getSettings();
            $income_cats = range(2,30);
            $income_locale = array('ru', 'en', 'pt');
            if ($my_settings instanceof UserSettings){
                if ($my_settings->getIncomeCats()){
                    $income_cats = array();
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
            }

            $categorized = array();
            $rush = array();
            if (!empty($income_locale)){
                $catQuery = $questionRepo->createQueryBuilder('question')
                    ->innerJoin('question.user', 'user')
                    ->where('question.category IN (:cid) AND question.datetime > :lastTime AND question.status <> :status AND question.isSpam = false AND question.user != :user AND question.locale IN (:locale)')
                    ->setParameter('cid', $income_cats)
                    ->setParameter('locale', $income_locale)
                    ->setParameter('lastTime', $user->getLastCatsView())
                    ->setParameter('status', '2')
                    ->setParameter('user', $user->getId())
                    ->getQuery();
                $categorized = $catQuery->getResult();

                foreach ($categorized as $cid=>$question) {
                    if (!$this->geoFilterQuestion($user, $question)){
                        unset($categorized[$cid]);
                    }
                }

                $rushQuery = $questionRepo->createQueryBuilder('question')
                    ->innerJoin('question.user', 'user')
                    ->where('question.category = :cid AND question.datetime > :lastTime AND question.status <> :status AND question.isSpam = false AND question.user != :user AND question.locale IN (:locale)')
                    ->setParameter('cid', '1')
                    ->setParameter('locale', $income_locale)
                    ->setParameter('lastTime', $user->getLastRushView())
                    ->setParameter('status', '2')
                    ->setParameter('user', $user->getId())
                    ->getQuery();
                $rush = $rushQuery->getResult();

                foreach ($rush as $rid=>$question) {
                    if (!$this->geoFilterQuestion($user, $question)){
                        unset($rush[$rid]);
                    }
                }

            }


            $data['new_categorized_questions'] = count($categorized);
            $data['new_rush_questions'] = count($rush);
            $data['questions_last_update'] = date('Y-m-d H:i:s');

//            if ($request->get('by_category')){
//                $html = '';
//                foreach ($categorized as $question) {
//                    $html .= '<div class="singleQuestion" data-id="'.$question->getId().'" data-user="'.$question->getUser()->getId().'">
//                    <div class="userName">'.$question->getUser().'</div>
//                    <div class="questionDate">'.$question->getDatetime()->format('d.m.Y h:i').'</div>
//                    <div style="clear: both;"></div>
//                    <div class="questionText">
//                        '.$question->getText().'
//                        <div class="socialIcons">
//                            <div title="'.$this->get('translator')->trans('отметить как спам').'" class="spamButton"></div>
//                            <div title="'.$this->get('translator')->trans('не нравится').'" class="likeButton"></div>
//                            <div title="'.$this->get('translator')->trans('добавить в заметки').'" class="repostButton"></div>
//                            <form class="answer">
//                                <input class="answerButton" type="button" value="'.$this->get('translator')->trans('ответить').'" data-alter-name="'.$this->get('translator')->trans('свернуть').'" />
//                            </form>
//                        </div>
//                    </div>
//                </div>';
//
//                }
//                $data['categorized_questions'] = $html;
//                $user->setLastCatsView(new \DateTime());
//
//                $em->persist($user);
//                $em->flush();
//            }
            if ($request->get('rush')){
                $html = '';
                foreach ($rush as $question) {
                    $html .= '<div class="singleQuestion" data-id="'.$question->getId().'" data-user="'.$question->getUser()->getId().'">
                    <div class="userName">'.$question->getUser().'</div>
                    <div class="questionDate">'.$question->getDatetime()->format('d.m.Y h:i').'</div>
                    <div style="clear: both;"></div>
                    <div class="questionText">
                        '.$question->getText().'
                        <div class="socialIcons">
                            <div title="'.$this->get('translator')->trans('отметить как спам').'" class="spamButton"></div>
                            <div title="'.$this->get('translator')->trans('не нравится').'" class="likeButton"></div>
                            <div title="'.$this->get('translator')->trans('добавить в заметки').'" class="repostButton"></div>
                            <form class="answer">
                                <input class="answerButton" type="button" value="'.$this->get('translator')->trans('ответить').'" data-alter-name="'.$this->get('translator')->trans('свернуть').'" />
                            </form>
                        </div>
                    </div>
                </div>
                <div class="myAnswer no-border"></div>
                <form class="answerForm">
                    <textarea class="answerTextarea" ></textarea>
                    <div class="answerFormNavigation">
                        <input class="cancelButton" type="button" value="'.$this->get('translator')->trans('отмена').'" />
                        <input class="submitAnswerButton" type="submit" value="'.$this->get('translator')->trans('отправить').'" />
                    </div>
                </form>';

                }
                $data['rush_questions'] = $html;
                $user->setLastRushView(new \DateTime());

                $em->persist($user);
                $em->flush();
            }
        }


        if ($request->get('update_my_questions')){
            if ($user instanceof User){
                $my_questions = $questionRepo->findAllNotClosedByUser($user, $this->container->get('request')->getClientIp());
                $my_updated_questions = array();
                $i = 0;
                foreach ($my_questions as $id => $my_question) {
                    $answers = $my_question->getAnswers();
                    $html = '';
                    $j = 0;
                    foreach ($answers as $ans) {
                        if (!$ans->getSpams()->contains($user)){
//                            if (!in_array($user, (array)$ans->getSpams())){
                                $html .= '<div class="singleAnswer" data-user="'.$ans->getUser()->getId().'" data-id="'.$ans->getId().'"><div class="userName">'.$ans->getUser()->getNick().'</div><div class="answerDate">'.$ans->getPubDate()->format("d.m.Y H:i").'</div><div style="clear: both;"></div><div class="questionText">'.$ans->getText().'<div class="socialIcons"><div class="spamButton '.($ans->getSpams()->contains($user) ? 'spamButtonActive' : '').'"></div><div class="likeButton '.(in_array($user, (array)$ans->getLikes()) ? 'likeButtonActive' : '').'"></div><div class="arrowButton inviteUser"></div><div class="letterButton sendMessage"></div></div></div></div>';
                                $j++;
//                            }
                        }
                    }
                    $my_updated_questions[$i]['id'] = $my_question->getId();
                    $my_updated_questions[$i]['answers'] = $html;
                    if ($j>=$my_question->getBoundary()){
                        $my_updated_questions[$i]['closed'] = true;
                        $my_question->setStatus(2);

                        $em->persist($my_question);
                        $em->flush();
                    } else {
                        $my_updated_questions[$i]['closed'] = false;
                    }
                    $i++;
                }
//                echo $html;
                $data['my_questions'] = $my_updated_questions;
            }
        }

        return new Response(json_encode($data, true));
    }

    public function deleteMyQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail", 403);
        }

        $em = $this->getDoctrine()->getManager();

        $question = $request->get("question");

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($question);
        $note_questions = $this->getDoctrine()->getRepository("CronCronBundle:NotesQuestion")->findBy(array("question"=>$question->getId()));
        $answers = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findBy(array("question"=>$question->getId()));

        $chat = $this->getDoctrine()->getRepository("CronCronBundle:Chat")->findOneBy(array("question"=>$question->getId()));
        if ($chat instanceof Chat){
            $chat_members = $this->getDoctrine()->getRepository("CronCronBundle:ChatMember")->findBy(array("chat"=>$chat->getId()));
            $chat_msgs = $this->getDoctrine()->getRepository("CronCronBundle:ChatMsg")->findBy(array("chat"=>$chat->getId()));
            $chat_srvmsgs = $this->getDoctrine()->getRepository("CronCronBundle:ChatSrvMsg")->findBy(array("chat"=>$chat->getId()));
            foreach ($chat_members as $chat_members1) {
                $em->remove($chat_members1);
            }
            foreach ($chat_msgs as $chat_msgs1) {
                $em->remove($chat_msgs1);
            }
            foreach ($chat_srvmsgs as $chat_srvmsgs1) {
                $em->remove($chat_srvmsgs1);
            }
            $em->remove($chat);
        }

        foreach ($note_questions as $note_questions1) {
            $em->remove($note_questions1);
        }
        foreach ($answers as $answers1) {
            $em->remove($answers1);
        }

        $em->remove($question);

        $user->setCredits($user->getCredits()-5);
        $em->persist($user);

        $em->flush();

        return new Response("SUCCESS");
    }

    public function closeMyQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
//            return new Response("Fail", 403);
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }

        $em = $this->getDoctrine()->getManager();

        $question = $request->get("question");

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($question);

        if ($question->getUser()==$user)
        {
            $question->setStatus(2);

            $em->persist($question);

            $em->flush();
        }



        return new Response("SUCCESS");
    }

    public function hideMyQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }

        $em = $this->getDoctrine()->getManager();

        $question = $request->get("question");

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($question);

        if ($question->getUser()==$user){
            $question->setHideOnIndex(true);

            $em->persist($question);

            $em->flush();
        }

        return new Response("SUCCESS");
    }

    public function hideIncomeQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }

        $em = $this->getDoctrine()->getManager();

        $question_id = $request->get("question_id");

        $my_answer = $this->getDoctrine()->getRepository("CronCronBundle:Answer")->findOneBy(array("question"=>$question_id, "user"=>$user->getId()));

        if ($my_answer instanceof Answer){
            $my_answer->setHideIncome(1);

            $em->persist($my_answer);

            $em->flush();
        }

        return new Response("SUCCESS");
    }

    public function ignoreQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }

        $em = $this->getDoctrine()->getManager();

        $question = $this->getDoctrine()->getRepository("CronCronBundle:Question")->find($request->get("question_id"));

        if ($question instanceof Question){
            $user->addIgnoredQuestion($question);

            $em->persist($user);

            $em->flush();
        }

        return new Response("SUCCESS");
    }

    public function deleteNotedQuestionAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');
        }

        $em = $this->getDoctrine()->getManager();

        $question_id = $request->get("question_id");

        $noted_question = $this->getDoctrine()->getRepository("CronCronBundle:NotesQuestion")->findOneBy(array("question"=>$question_id, "user"=>$user->getId()));

        if ($noted_question instanceof \Cron\CronBundle\Entity\NotesQuestion){
            $em->remove($noted_question);
            $em->flush();
        }

        return new Response("SUCCESS");
    }

    public function deleteMyLinkAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response("Fail");
        }

        $em = $this->getDoctrine()->getManager();

        $user_link = $request->get("id");
//        if (!$user_link){
//            $user_link =
//        }

        $user_link = $this->getDoctrine()->getRepository("CronCronBundle:UserLink")->find($user_link);

        if ($user_link->getUser()==$user){
            $em->remove($user_link);

            $em->flush();
        }

        return new Response("SUCCESS");
    }

    public function getLastSpamQuestionsAction(Request $request)
    {
        $lastTime = $request->get("questions_last_update");
//        $lastTime = "2013-01-20 00:00:00";

        $em = $this->getDoctrine()->getManager();

        $questionsRepo = $this->getDoctrine()->getRepository('CronCronBundle:Question');
        $query = $questionsRepo->createQueryBuilder('question')
            ->innerJoin('question.user', 'user')
            ->where('question.datetime > :lastTime AND question.status <> :status AND question.isSpam = false AND question.amnestied = false')
            ->setParameter('lastTime', $lastTime)
            ->setParameter('status', '2')
            ->getQuery();
        $questions = $query->getResult();


        $data = array();
        $html = '';
        foreach ($questions as $question) {
            if ($question->getSpams()->count()){
                $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user"=>$question->getUser()->getId()));
                $question->question_count = count($user_questions);
                $html .= '<div class="singleQuestion" data-id="'.$question->getId().'" data-user="'.$question->getUser()->getId().'">
                    <div class="userName">'.$question->getUser().'</div>
                    <div class="questionDate">'.$question->getDatetime()->format('d.m.Y h:i').'</div>
                    <div style="clear: both;"></div>
                    <div class="questionText">
                        '.$question->getText().'
                        <div class="socialIcons">
                            <div class="spam-index">Спам-индекс: '.$question->getUser()->getSpamActivity().'</div>
                            <div class="questions-count">Вопросов: '.$question->question_count.'</div>
                            <a href="#" class="confirm-spam">Подтвердить</a>
                            <a href="#" class="cancel-spam">Отмена</a>
                            <a href="#" class="block-user">Блок пользователя</a>
                        </div>
                    </div>
                </div>';
            }

        }

        $data['questions'] = $html;
        $data['questions_last_update'] = date('Y-m-d H:i:s');

        return new Response(json_encode($data, true));
    }

    public function getLastSpamAnswersAction(Request $request)
    {
        $lastTime = $request->get("questions_last_update");
//        $lastTime = "2013-01-20 00:00:00";

        $em = $this->getDoctrine()->getManager();

        $answersRepo = $this->getDoctrine()->getRepository('CronCronBundle:Answer');
        $query = $answersRepo->createQueryBuilder('answer')
            ->innerJoin('answer.user', 'user')
            ->where('answer.datetime > :lastTime AND answer.status <> :status AND answer.isSpam = false AND answer.amnestied = false')
            ->setParameter('lastTime', $lastTime)
            ->setParameter('status', '2')
            ->getQuery();
        $answers = $query->getResult();


        $data = array();
        $html = '';
        foreach ($answers as $answer) {
            if ($answer->getSpams()->count()){
                $user_questions = $this->getDoctrine()->getRepository("CronCronBundle:Question")->findBy(array("user"=>$answer->getUser()->getId()));
                $answer->question_count = count($user_questions);
                $html .= '<div class="singleQuestion" data-id="'.$answer->getId().'" data-user="'.$answer->getUser()->getId().'">
                    <div class="userName">'.$answer->getUser().'</div>
                    <div class="questionDate">'.$answer->getDatetime()->format('d.m.Y h:i').'</div>
                    <div style="clear: both;"></div>
                    <div class="questionText">
                        '.$answer->getText().'
                        <div class="socialIcons">
                            <div class="spam-index">Спам-индекс: '.$answer->getUser()->getSpamActivity().'</div>
                            <div class="questions-count">Вопросов: '.$answer->question_count.'</div>
                            <a href="#" class="confirm-spam">Подтвердить</a>
                            <a href="#" class="cancel-spam">Отмена</a>
                            <a href="#" class="block-user">Блок пользователя</a>
                        </div>
                    </div>
                </div>';
            }

        }

        $data['questions'] = $html;
        $data['questions_last_update'] = date('Y-m-d H:i:s');

        return new Response(json_encode($data, true));
    }

    public function getBoundaryPriceAction(Request $request)
    {
        $admin_settings = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);
        $price = 5;
        switch($request->get('boundary')){
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

        return new Response($price);
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

