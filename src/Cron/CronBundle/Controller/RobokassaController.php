<?php

namespace Cron\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cron\CronBundle\Entity\User;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RobokassaController extends AbstractController
{

    protected $mrh_login = "login";

    protected $mrh_pass1 = "pass1";

    protected $mrh_pass2 = "pass2";

    protected $mrh_host = "http://test.robokassa.ru/Index.aspx";//https://merchant.roboxchange.com/Index.aspx


    public function payAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->redirect("/");
        }

        // номер заказа
        $inv_id = time()*count($user->getUsername());
//        $_SESSION['inv_id'] = $inv_id;

        // описание заказа
        $inv_desc = "";
        // сумма заказа
        $creditCurrency = $this->getCreditCurrency();
        $out_summ = 5* $creditCurrency;

        // тип товара
        $shp_item = "1";

        // предлагаемая валюта платежа
        $in_curr = "";

        // язык
        $culture = "ru";

        $encoding = "utf-8";

        $crc  = md5("$this->mrh_login:$out_summ:$inv_id:$this->mrh_pass1:Shp_item=$shp_item");


        $roboform =
            '<form action="'.$this->mrh_host.'" method="POST">'.
            '<input type="hidden" name="MrchLogin" value="'.$this->mrh_login.'">'.
            '<input type="hidden" name="InvId" value="'.$inv_id.'">'.
            '<input type="hidden" name="Desc" value="'.$inv_desc.'">'.
            '<input type="hidden" id="creditCurrency" name="Currency" value="'. $creditCurrency .'">'.
            '<label for="CreditsAmount">Количество кредитов</label> <select name="CreditsAmount" id="CreditsAmount"><option value="5">5</option><option value="10">10</option><option value="50">50</option><option value="100">100</option><option value="200">200</option><option value="500">500</option></select><br/>'.
            '<label for="OutSum">Сумма в руб.</label> <input type="text" id="OutSum" disabled="disabled" name="OutSum" value="'.$out_summ.'"><br/>'.
            '<input type="hidden" name="SignatureValue" value="'.$crc.'">'.
            '<input type="hidden" name="Shp_item" value="'.$shp_item.'">'.
            '<input type="hidden" name="IncCurrLabel" value="'.$in_curr.'">'.
            '<input type="hidden" name="Encoding" value="'.$encoding.'">'.
            '<input type="hidden" name="Culture" value="'.$culture.'">'.
            '<input type="submit" class="pay-submit" value="оплатить">'.
            '<input type="button" class="pay-cancel" value="отмена">'.
            '</form>';
        return $this->render('CronCronBundle:Main:credits.html.twig', array(
            'roboform' => $roboform,
            'title' => 'Кредиты сайта',
            'curUser' => $user,
            'onlineUserCount' => $this->onlineUserCount, 'totalUserCount' => $this->totalUserCount
        ));
    }

    public function resultAction(Request $request)
    {
        $out_summ = $request->get("OutSum");
        $inv_id = $request->get("InvId");
        $shp_item = $request->get("Shp_item");
        $crc = $request->get("SignatureValue");

        $crc = strtoupper($crc);

        $my_crc = strtoupper(md5("$out_summ:$inv_id:$this->mrh_pass2:Shp_item=$shp_item"));

        if ($my_crc !=$crc){
            return $this->redirect("/pay");
        } else {
            return $this->redirect("/adverts");
        }

    }

    public function successAction(Request $request)
    {
        $out_summ = $request->get("OutSum");
        $inv_id = $request->get("InvId");
        $shp_item = $request->get("Shp_item");
        $crc = $request->get("SignatureValue");

        $crc = strtoupper($crc);

        $my_crc = strtoupper(md5("$out_summ:$inv_id:$this->mrh_pass1:Shp_item=$shp_item"));

        if ($my_crc != $crc){
//            echo "bad sign\n";
//            exit();
        }
    }

    public function failAction(Request $request)
    {
        return $this->redirect("/pay");
    }

    public function getCreditCurrency()
    {
        $admin_settings = $this->getDoctrine()->getRepository("CronCronBundle:AdminSettings")->find(1);

        return $admin_settings->getCreditCurrency();
    }
}
