<?php

namespace AppBundle\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',\Symfony\Component\Form\Extension\Core\Type\TextType::class,
                ['label'=>'Titre','required'=>true])
            ->add('text',TextareaType::class,
                [
                    'label'=>'Texte',
                    'required'=>true,
                    'attr'=>['rows'=>10]
                    ])
            ->add('createdAt', DateType::class,
                ['label'=>'Date de publication','widget'=>'single_text'])
            ->add('image', FileType::class,
                ['label'=>'Image',
                    "required"=>false,
                    'data_class'=>'Symfony\Component\HttpFoundation\File\File',
                    'property_path'=>'image'
                ])
         /*  ->add('author',EntityType::class,[
               'class'=>'AppBundle\Entity\Author',
               'placeholder'=>'Choisssisez un auteur',
               'choice_label'=>'fullName']) */
         /*   ->add('theme')*/
            ->add('submit',SubmitType::class,
                ['label'=>'Valider']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Post'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_post';
    }


}
