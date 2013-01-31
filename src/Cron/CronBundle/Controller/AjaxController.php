<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cron\CronBundle\Entity\Answer;
use Cron\CronBundle\Entity\File;
use Cron\CronBundle\Entity\User;

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
                                     ->where('question.category > :cid AND question.datetime > :lastTime AND question.status <> :status')
                                     ->setParameter('cid', '1')
                                     ->setParameter('lastTime', $lastTime)
                                     ->setParameter('status', '2')
                                     ->getQuery();
            $categorized = $catQuery->getResult();

            $rushQuery = $questionRepo->createQueryBuilder('question')
                                      ->innerJoin('question.user', 'user')
                                      ->where('question.category = :cid AND question.datetime > :lastTime AND question.status <> :status')
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
            if (!$question instanceof \Cron\CronBundle\Entity\Question)
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
            if ($questionId = $request->get("question_id"))
            {
                $question = $this->getDoctrine()->getRepository('CronCronBundle:Question')->findOneById($questionId);
                if (!$question instanceof \Cron\CronBundle\Entity\Question)
                    return new Response('Fail');

                $user = $this->getUser();
                if (!$user instanceof User)
                    return new Response('Fail');
                    //$user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername('Guest');

                $question->addLikes($user);

                return new Response('Succsess');
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

            return new Response('SUCCESS');
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
                        <td>'.$last_file->getFilename().'</td>
                        <td><div class="my-file-size">'.$last_file->getFilesize().'</div></td>
                        <td width="155"><input type="button" class="delete-my-file" value="удалить"><input type="button" class="download-my-file" value="загрузить"></td>
                    </tr>
                </table>
            </div>';
        return new Response($html);
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
