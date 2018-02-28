<?php

namespace AppBundle\Form;

use function PHPSTORM_META\type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AuthorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', TextType::class,['label'=>'Prénom'])
            ->add('name', TextType::class, ['label'=>'Nom'])
            ->add('email', RepeatedType::class,['type'=> EmailType::class,
                'first_options'=>['label'=>'Saisir Email','required'=>true],'second_options'=>['label'=>'Confirmer l\'email'],'invalid_message'=>'La saisie doit être identique'])
            ->add('plainPassword', RepeatedType::class,['type'=> PasswordType::class,
                'first_options'=>['label'=>'Saisir le mot de passe','required'=>true],'second_options'=>['label'=>'Confirmer le mot de passe'],'invalid_message'=>'Le mot de passe n\'est pas identique'])
            ->add('submit',SubmitType::class,
                ['label'=>'Valider','attr'=>['class'=>'btn btn-primary']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Author'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_author';
    }


}
