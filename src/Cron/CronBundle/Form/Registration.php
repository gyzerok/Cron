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
        for ($i = 1950; $i <= date('Y'); $i++)
            $years[$i] = $i;

        $builder->add('email', null, array('label' => 'Email', 'required' => true))
                ->add('userpass', null, array('label' => 'Пароль', 'required' => true))
                ->add('userpassAgain', 'text', array('label' => 'Повторите пароль', 'required' => true))
                ->add('username', null, array('label' => 'Ваше имя', 'required' => true))
                ->add('gender', 'choice', array('label' => 'Пол', 'choices' => array(1 => 'Мужской', 2 => 'Женский'), 'expanded' => true, 'required' => true))
                ->add('day', 'choice', array('label' => 'День', 'choices' => $days, 'expanded' => true, 'required' => true))
                ->add('month', 'choice', array('label' => 'Месяц', 'choices' => $months, 'expanded' => true, 'required' => true))
                ->add('year', 'choice', array('label' => 'Год', 'choices' => $years, 'expanded' => true, 'required' => true))
                ->add('country', null, array('label' => 'Страна', 'empty_value' => 'Выберите страну', 'required' => true))
                ->add('state', null, array('label' => 'Регион', 'empty_value' => 'Выберите регион', 'disabled' => true, 'required' => true))
                ->add('city', null, array('label' => 'Город', 'empty_value' => 'Выберите город', 'disabled' => true, 'required' => true));
    }

    public function getName()
    {
        return 'register';
    }
}