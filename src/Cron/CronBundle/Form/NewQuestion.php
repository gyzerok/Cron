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
        $builder->add('text', 'textarea', array('label' => 'Вопрос', 'max_length' => 130))
                ->add('category', null, array('label' => 'Категория', 'expanded' => true))
                ->add('private', 'checkbox', array('label' => 'закрытый', 'required' => false))
                ->add('country', null, array('label' => 'Страна', 'empty_value' => 'Все страны'))
                ->add('state', null, array('label' => 'Регион', 'empty_value' => 'Все регионы', 'disabled' => true))
                ->add('city', null, array('label' => 'Город', 'empty_value' => 'Все города', 'disabled' => true))
                ->add('boundary', 'choice', array('label' => 'Минимальная наполняемость ответами', 'choices' => array(10 => '10', 20 => '20', 30 => '30', 40 => '40', 50 => '50')));

        //$builder->add('country')->add('state');

        /*$factory = $builder->getFormFactory();

        $refreshStates = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('entity','state', null, array(
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

        $setCountry = function ($form, $country) use ($factory)
        {
            $form->add($factory->createNamed('entity', 'country', null, array(
                'class'         => 'CronBundle:Country',
                'property'      => 'country',
                //'property_path' => false,
                'empty_value'   => 'Все страны',
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
