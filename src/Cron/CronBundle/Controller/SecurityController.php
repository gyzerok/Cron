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
			'totalUserCount' => $this->totalUserCount,
			'onlineUserCount' => $this->onlineUserCount
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
        if ($request->isMethod('POST'))
        {
            $email = $request->get('email');
            $user = $this->getDoctrine()->getRepository('CronCronBundle:User')->findOneByUsername($email);

            if ($user instanceof \Cron\CronBundle\Entity\User)
            {
                $hash = md5($user->getId() + $user->getNick() + $user->getUsername()) . '_' . md5($user->getPassword());

                $mailer = $this->get('mailer');
                $translator = $this->get('translator');
                $message = \Swift_Message::newInstance(null, null, "text/html")
                    ->setSubject($translator->trans('Восстановление пароля'))
                    ->setFrom("aditus777@gmail.com")
                    ->setTo($user->getUsername())
                    ->setBody('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body>' .
                    $translator->trans('Здравствуйте!').'<br><br>' .
                    $translator->trans('Перейдите по ссылке для изменения вашего пароля').':<br><a href="http://' . $_SERVER['HTTP_HOST'] . '/change_password?id=' . $user->getId() . '&hash=' . $hash . '">http://' . $_SERVER['HTTP_HOST'] . '/change_password?id=' . $user->getId() . '&hash=' . $hash . '</a><br>' .
                    '('.$translator->trans('если не можете нажать на нее, скопируйте ее в адресную строку Вашего браузера').')<br><br>' .
                    $translator->trans('С уважением, служба поддержки aditus.ru').'<br>' .
                    '</body></html>');
                $mailer->send($message);

                return $this->render('CronCronBundle:Main:info.html.twig', array('info' => $this->get('translator')->trans('Сообщение отправлено на').' ' . $user->getUsername(), 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
            }

            return $this->render('CronCronBundle:Main:info.html.twig', array('info' => 'Такого пользователя не существует', 'totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
        }

        return $this->render('CronCronBundle:Security:forgot_password.html.twig', array('totalUserCount' => $this->totalUserCount, 'onlineUserCount' => $this->onlineUserCount, 'curUser' => $this->user));
    }
}
