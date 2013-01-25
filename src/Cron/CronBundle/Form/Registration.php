<?php
namespace Cron\CronBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvents;
use Cron\CronBundle\Entity\Country;
use Cron\CronBundle\Entity\State;
use Doctrine\ORM\EntityRepository;

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

        $builder->add('username', 'email', array('label' => 'Email', 'required' => true))
                ->add('password', 'repeated', array('required' => true, 'first_name' => 'Password', 'second_name' => 'Confirm', 'type' => 'password'))
                ->add('nick', null, array('label' => 'Ваше имя', 'required' => true))
                ->add('gender', 'choice', array('label' => 'Пол', 'choices' => array(1 => 'Мужской', 2 => 'Женский'), 'expanded' => true, 'required' => true))
                ->add('birthDate', 'birthday', array('label' => 'Дата рождения', 'format' => 'dd MMM yyyy', 'years' => $years, 'required' => true))
                ->add('country', 'entity', array('label' => 'Страна', 'class' => 'CronCronBundle:Country', 'property' => 'name', 'empty_value' => 'Все страны', 'required' => true))
                ->add('state', 'entity', array('label' => 'Регион', 'class' => 'CronCronBundle:State', 'property' => 'name', 'empty_value' => 'Все регионы', 'disabled' => true, 'required' => true))
                ->add('city', 'entity', array('label' => 'Город', 'class' => 'CronCronBundle:City', 'property' => 'name', 'empty_value' => 'Все города', 'disabled' => true, 'required' => true))
                ->add('agreement', null, array('label' => 'Правила', 'required' => true));

        $factory = $builder->getFormFactory();

        $refreshStates = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('state', 'entity', null, array(
                'class'         => 'Cron\CronBundle\Entity\State',
                'property'      => 'state',
                'empty_value'   => 'Все регионы',
                'query_builder' => function (EntityRepository $repository) use ($country)
                {
                    $qb = $repository->createQueryBuilder('state')
                        ->innerJoin('state.country', 'country');

                    if($country instanceof Country){
                        $qb->where('state.country = :country')
                            ->setParameter('country', $country);
                    }elseif(is_numeric($country)){
                        $qb->where('country.id = :country')
                            ->setParameter('country', $country);
                    }else{
                        $qb->where('country.name = :country')
                            ->setParameter('country', null);
                    }
                    return $qb;
                }
            )));
        };

        $refreshCities = function ($form, $state) use ($factory)
        {
            $form->add($factory->createNamed('city', 'entity', null, array(
                'class'         => 'Cron\CronBundle\Entity\City',
                'property'      => 'city',
                'empty_value'   => 'Все регионы',
                'query_builder' => function (EntityRepository $repository) use ($state)
                {
                    $qb = $repository->createQueryBuilder('city')
                        ->innerJoin('city.state', 'state');

                    if($state instanceof State){
                        $qb->where('city.state = :state')
                            ->setParameter('state', $state);
                    }elseif(is_numeric($state)){
                        $qb->where('state.id = :state')
                            ->setParameter('state', $state);
                    }else{
                        $qb->where('state.name = :state')
                            ->setParameter('state', null);
                    }
                    return $qb;
                }
            )));
        };

        $setCountry = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('entity', 'country', null, array(
                'class'         => 'CronCronBundle:Country',
                'property'      => 'country',
                'property_path' => false,
                'empty_value'   => 'Все страны',
                'data'          => $country,
            )));
        };

        $setState = function ($form, $state) use ($factory)
        {
            $form->add($factory->createNamed('entity', 'state', null, array(
                'class'         => 'CronCronBundle:State',
                'property'      => 'state',
                'property_path' => false,
                'empty_value'   => 'Все регионы',
                'data'          => $state,
            )));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (DataEvent $event) use ($refreshStates, $refreshCities, $setCountry, $setState)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if($data == null)
                return;

            $country = ($data->getId()) ? $data->getCountry() : null;
            if($country instanceof Country){
                $refreshStates($form, $country);
                $setCountry($form, $country);
            }

            $state = ($data->getId()) ? $data->getState() : null;
            if($state instanceof State){
                $refreshCities($form, $state);
                $setState($form, $state);
            }
        });

        $builder->addEventListener(FormEvents::PRE_BIND, function (DataEvent $event) use ($refreshStates, $refreshCities)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if(array_key_exists('country', $data)) {
                $refreshStates($form, $data['country']);
            }

            if(array_key_exists('state', $data)) {
                $refreshCities($form, $data['state']);
            }
        });
    }

    public function getName()
    {
        return 'register';
    }
}