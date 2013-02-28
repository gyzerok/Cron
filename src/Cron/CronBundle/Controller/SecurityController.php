<?php
namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends AbstractController
{
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // получить ошибки логина, если таковые имеются
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('CronCronBundle:Security:login.html.twig', array(
            'curUser'       => $this->getUser(),
            // имя, введённое пользователем в последний раз
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }

    public function changePasswordAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($request->isMethod('GET'))
        {
            $id = $request->get('id');
            $arg = $request->get('hash');
            $hash = preg_split('[_]', $arg);
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneById($id);
            if ($hash[0] == md5($user->getId() + $user->getNick() + $user->getUsername()) && $hash[1] == md5($user->getPassword()))
                return $this->render('CronCronBundle:Security:change_password.html.twig', array('id' => $user->getId(), 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user, 'error' => null));
        }
        if ($request->isMethod('POST'))
        {
            $id = $request->get('id');
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneById($id);
            if (!$user instanceof \Cron\CronBundle\Entity\User)
                return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Ошибка', 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));

            if ($request->get('password') == $request->get('cpassword'))
                $password = $request->get('password');
            else
                return $this->render('CronCronBundle:Security:change_password.html.twig', array('totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user, 'error' => 'Введенные пароли не совпадают'));

            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());
            $user->setPassword($password);

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Пароль успешно изменен', 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
        }

        return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Ошибка', 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
    }

    public function forgotPasswordAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $email = $request->get('email');
        $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername($email);

        if ($user instanceof \Cron\CronBundle\Entity\User)
        {
            $hash = md5($user->getId() + $user->getNick() + $user->getUsername()) . '_' . md5($user->getPassword());

            $mailer = $this->get('mailer');
            $message = \Swift_Message::newInstance(null, null, "text/html")
                ->setSubject('Обратная связь')
                ->setFrom("aditus777@gmail.com")
                ->setTo($user->getUsername())
                ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' .
                $_SERVER['HTTP_HOST'] . '/change_password?id=' . $user->getId() . '&hash=' . $hash .
                '</body></html>');
            $mailer->send($message);

            return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Сообщение отправлено на ' . $user->getUsername(), 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
        }

        return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Такого пользователя не существует', 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
    }
}
