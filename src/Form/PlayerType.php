<?php

namespace App\Form;

use App\Entity\choice;
use App\Entity\Player;
use App\Entity\team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forename')
            ->add('name')
            ->add('rate')
            ->add('team', EntityType::class, [
                'class' => team::class,
                'choice_label' => 'id',
            ])
            ->add('choice', EntityType::class, [
                'class' => choice::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
