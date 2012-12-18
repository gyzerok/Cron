<?php

namespace Cron\CronBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;
//use Symfony\Component\Form\Event\DataEvent;
//use Cron\CronBundle\Entity\Country;
//use Cron\CronBundle\Entity\State;
//use Cron\CronBundle\Entity\City;

class NewQuestion extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('text', 'textarea', array(/*'class'=>"question",*/ 'label' => 'Вопрос'))
                ->add('category', null, array('label' => 'Категория', 'expanded' => true))
                ->add('private', 'checkbox', array('label' => 'Закрытый', 'required' => false))
                ->add('country', null, array('label' => 'Страна', 'empty_value' => 'Все страны'))
                ->add('state', null, array('label' => 'Регион', 'empty_value' => 'Все регионы', 'disabled' => true))
                ->add('city', null, array('label' => 'Город', 'empty_value' => 'Все города', 'disabled' => true))
                ->add('boundary', 'choice', array('label' => 'Минимальная наполняемость ответами', 'choices' => array(1 => '10', 2 => '20', 3 => '30', 4 => '40', 5 => '50')));

        /*$builder->add('name');

        $factory = $builder->getFormFactory();

        $refreshStates = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('entity','state', null, array(
                'class'         => 'Cron\CronBundle\Entity\State',
                'property'      => 'name',
                'empty_value'   => '-- Select a state --',
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

        $setCountry = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('entity', 'country', null, array(
                'class'         => 'CronBundle:Country',
                'property'      => 'name',
                'property_path' => false,
                'empty_value'   => '-- Select a country --',
                'data'          => $country,
            )));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (DataEvent $event) use ($refreshStates, $setCountry)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if($data == null)
                return;

            if($data instanceof City){
                $country = ($data->getId()) ? $data->getState()->getCountry() : null ;
                $refreshStates($form, $country);
                $setCountry($form, $country);
            }
        });

        $builder->addEventListener(FormEvents::PRE_BIND, function (DataEvent $event) use ($refreshStates)
        {
            $form = $event->getForm();
            $data = $event->getData();

            if(array_key_exists('country', $data)) {
                $refreshStates($form, $data['country']);
            }
        });*/
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
