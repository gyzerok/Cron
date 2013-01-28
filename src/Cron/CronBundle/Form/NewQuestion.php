<?php

namespace Cron\CronBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\DataEvent;
use Cron\CronBundle\Entity\Country;
use Cron\CronBundle\Entity\State;
use Doctrine\ORM\EntityRepository;

class NewQuestion extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('text', 'textarea', array('label' => 'Вопрос', 'max_length' => 130))
                ->add('category', null, array('label' => 'Категория', 'expanded' => true))
                ->add('private', 'checkbox', array('label' => 'закрытый', 'required' => false))
                ->add('country', 'entity', array('label' => 'Страна', 'class' => 'CronCronBundle:Country', 'property' => 'name', 'empty_value' => 'Любая страна', 'required' => false))
                ->add('state', 'entity', array('label' => 'Регион', 'class' => 'CronCronBundle:State', 'property' => 'name', 'empty_value' => 'Любой регион', 'disabled' => true, 'required' => false))
                ->add('city', 'entity', array('label' => 'Город', 'class' => 'CronCronBundle:City', 'property' => 'name', 'empty_value' => 'Любой город', 'disabled' => true, 'required' => false))
                ->add('boundary', 'choice', array('label' => 'Минимальная наполняемость ответами', 'choices' => array(10 => '10', 20 => '20', 30 => '30', 40 => '40', 50 => '50')));

        $factory = $builder->getFormFactory();

        $refreshStates = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('state', 'entity', null, array(
                'class'         => 'Cron\CronBundle\Entity\State',
                'property'      => 'name',
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
                'property'      => 'name',
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
            $form->add($factory->createNamed('country', 'entity', null, array(
                'class'         => 'CronCronBundle:Country',
                'property'      => 'name',
                'property_path' => false,
                'empty_value'   => 'Все страны',
                'data'          => $country,
            )));
        };

        $setState = function ($form, $state) use ($factory)
        {
            $form->add($factory->createNamed('state', 'entity', null, array(
                'class'         => 'CronCronBundle:State',
                'property'      => 'name',
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
        return 'question';
    }

    /*public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Cron\CronBundle\Entity\Question');
    }*/
}
