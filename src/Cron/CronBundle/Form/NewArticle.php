<?php
namespace Cron\CronBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewArticle extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('header', 'text', array('label' => 'Заголовок', 'required' => true))
            ->add('locale', 'choice', array('label' => 'Язык', 'choices' => array('ru_RU' => 'Русский', 'en_US' => 'English', 'pt_PT' => 'Português'), 'expanded' => false, 'required' => true))
            ->add('category', 'entity', array('label' => 'Выберите категорию ...', 'class' => 'CronCronBundle:ArticleCategory', 'property' => 'name', 'empty_value' => '', 'required' => false))
            ->add('new_category', 'text', array('label' => '... или создайте новую', 'required' => false))
            ->add('img1', 'file', array('label' => 'Изображение 1 *', 'required' => false))
            ->add('img2', 'file', array('label' => 'Изображение 2 (необязательно)', 'required' => false))
            ->add('img3', 'file', array('label' => 'Изображение 3 (необязательно)', 'required' => false))
            ->add('text', 'textarea', array('label' => 'Текст статьи', 'trim' => true, 'required' => false))
            ->add('link_type', 'choice', array('label' => 'Тип ссылки', 'choices' => array('1' => 'Источник', '2' => 'Ссылка', '3' => 'Автор статьи'), 'expanded' => true, 'required' => true))
            ->add('link_value', 'text', array('label' => 'Ссылка', 'required' => true));
    }

    public function getName()
    {
        return 'article';
    }

}