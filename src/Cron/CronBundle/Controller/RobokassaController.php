<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cron\CronBundle\Entity\User;
use Cron\CronBundle\Entity\Payment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RobokassaController extends AbstractController
{

    protected $mrh_login = "vanlob";

    protected $mrh_pass1 = "Gen11Dcare";

    protected $mrh_pass2 = "4etteFlo";

    protected $mrh_host = "http://test.robokassa.ru/Index.aspx";//https://merchant.roboxchange.com/Index.aspx


    public function payAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->redirect("/");
        }

//        $payment = new Payment();
//        $payment->setUser($user)
//            ->setDatetime(new \DateTime());

        // номер заказа
        $inv_id = time()*count($user->getUsername());
//        $payment->setHash($inv_id);
//        $_SESSION['inv_id'] = $inv_id;

        // описание заказа
        $inv_desc = "Покупка кредитов на aditus.com";

        // сумма заказа
        $creditCurrency = $this->getCreditCurrency();
        $out_summ = "500";
//        $payment->setSum($out_summ);

        // тип товара
        $shp_item = "2";

        // предлагаемая валюта платежа
        $in_curr = "";

        // язык
        $culture = "ru";

        $encoding = "utf-8";

        $crc  = md5("$this->mrh_login:$out_summ:$inv_id:$this->mrh_pass1:Shp_item=$shp_item");

        $roboform =
            '<form action="'.$this->mrh_host.'" method="POST" id="roboform">'.
            '<input type="hidden" name="MrchLogin" value="'.$this->mrh_login.'">'.
            '<input type="hidden" id="InvId" name="InvId" value="'.$inv_id.'">'.
            '<input type="hidden" name="Desc" value="'.$inv_desc.'">'.
//                '<p><label for="" class="field">Сумма в руб.</label> <input type="text" disabled="disabled" name="OutSum" value="'.$out_summ.'"></p>'.
//                '<input type="text" name="Desc" value="'.$inv_desc.'">'.
//            '<input type="hidden" id="creditCurrency" name="Currency" value="'. $creditCurrency .'">'.
//            '<label for="CreditsAmount">Количество кредитов</label> <select id="CreditsAmount"><option value="5">5</option><option value="10">10</option><option value="50">50</option><option value="100">100</option><option value="200">200</option><option value="500">500</option></select><br/>'.
            '<label for="OutSum">Сумма в руб.</label> <input type="text" id="OutSum" name="OutSum" disabled="disabled" value="'.$out_summ.'"><br/>'.
            '<input type="hidden" id="Shp_item" name="Shp_item" value="'.$shp_item.'">'.
//            '<input type="hidden" name="IncCurrLabel" value="'.$in_curr.'">'.
//            '<input type="hidden" name="Encoding" value="'.$encoding.'">'.
//            '<input type="hidden" name="Culture" value="'.$culture.'">'.
            '<input type="hidden" name="SignatureValue" value="'.$crc.'">'.
            '<input type="submit" id="robo-submit" style="margin: 0 auto;float:none;" value="оплатить"> '.
            '<input type="submit" class="pay-cancel" style="margin: 0 auto;float:none;" value="отмена">'.
            '</form>';

//        $em = $this->getDoctrine()->getManager();
//        $em->persist($payment);
//        $em->flush();

        return $this->render('CronCronBundle:Main:credits.html.twig', array(
            'roboform' => $roboform,
            'title' => 'Кредиты сайта',
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function prepareAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return new Response('Fail');
        }

        $out_summ = $request->get("OutSum");
        $inv_id = $request->get("InvId");

        $payment = new Payment();
        $payment->setUser($user)
            ->setDatetime(new \DateTime())
            ->setHash($inv_id)
            ->setSum($out_summ);

        $em = $this->getDoctrine()->getManager();
        $em->persist($payment);
        $em->flush();

        return new Response('SUCCESS');
    }

    public function updateSignatureAction(Request $request)
    {
        $out_summ = $request->get("OutSum");
        $inv_id = $request->get("InvId");
        $shp_item = $request->get("Shp_item");

        $new_crc = md5("$this->mrh_login:$out_summ:$inv_id:$this->mrh_pass1:Shp_item=$shp_item");

        return new Response($new_crc);
    }

    public function resultAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->redirect("/");
        }

        $out_summ = $request->get("OutSum");
        $inv_id = $request->get("InvId");
        $shp_item = $request->get("Shp_item");
        $crc = $request->get("SignatureValue");

        $crc = strtolower($crc);

        $my_crc = strtolower(md5("$out_summ:$inv_id:$this->mrh_pass2:Shp_item=$shp_item"));

//        if ($my_crc !=$crc){
//            return $this->redirect("/credits?fail=1");
//        } else {
//            $payment = $this->getDoctrine()->getRepository("CronCronBundle:Payment")->findOneBy(array('hash' => $my_crc, 'user' => $user->getId()));
//            $payment->setPaid(1);
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($payment);
//            $em->flush();

            return $this->render('CronCronBundle:Main:pay_success.html.twig', array(
                'title' => 'Оплата успешно произведена',
                'curUser' => $user,
                'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
            ));
//        }

    }

    public function successAction(Request $request)
    {
//        $out_summ = $request->get("OutSum");
//        $inv_id = $request->get("InvId");
//        $shp_item = $request->get("Shp_item");
//        $crc = $request->get("SignatureValue");
//
//        $crc = strtoupper($crc);
//
//        $my_crc = strtoupper(md5("$out_summ:$inv_id:$this->mrh_pass1:Shp_item=$shp_item"));
//
//        if ($my_crc != $crc){
////            echo "bad sign\n";
////            exit();
//        }
    }

    public function failAction(Request $request)
    {
        return $this->redirect("/credits?fail=1");
    }

    public function getCreditCurrency()
    {
        $admin_settings = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);

        return $admin_settings->getCreditCurrency();
    }
}
