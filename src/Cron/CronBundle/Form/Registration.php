<?php
namespace Cron\CronBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class Registration extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $days = array();
        for ($i = 1; $i <= 31; $i++)
            $days[$i] = $i;
        $months = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $years = array();
        for ($i = date('Y') - 62; $i <= date('Y'); $i++)
            $years[$i] = $i;

        $builder->add('email', 'email', array('label' => 'Email', 'required' => true))
                ->add('password', 'repeated', array('required' => true, 'first_name' => 'Password', 'second_name' => 'Confirm', 'type' => 'password'))
                ->add('username', null, array('label' => 'Ваше имя', 'required' => true))
                ->add('gender', 'choice', array('label' => 'Пол', 'choices' => array(1 => 'Мужской', 2 => 'Женский'), 'expanded' => true, 'required' => true))
                ->add('birthDate', 'birthday', array('label' => 'Дата рождения', 'format' => 'dd MM yyyy', 'years' => $years, 'required' => true))
                ->add('country', null, array('label' => 'Страна', 'empty_value' => 'Выберите страну', 'required' => true))
                ->add('state', null, array('label' => 'Регион', 'empty_value' => 'Выберите регион', 'disabled' => true, 'required' => true))
                ->add('city', null, array('label' => 'Город', 'empty_value' => 'Выберите город', 'disabled' => true, 'required' => true))
                ->add('agreement', null, array('label' => 'Правила', 'required' => true));

    }

    public function getName()
    {
        return 'register';
    }
}