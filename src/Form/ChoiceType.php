<?php

namespace App\Form;

use App\Entity\Choice;
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\Week;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('week_id', EntityType::class, [
                'class' => Week::class,
                'choice_label' => 'id',
            ])

            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'save'],
            ]);
            
        ;

        $builder->get('week_id')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                $this->addTeamField($form->getParent(), $form->getData());
            });

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function(FormEvent $event) {
                $data = $event->getData();
                /* @var $players Player */
                $players = $data->getPlayers();
                // ca doit retourner null au debut et faire la suite du else
                
                $form = $event->getForm();

                if ($players && !$players->isEmpty()) {
                    $team = $players[0]->getTeam();
                    $league = $team->getLeague();
                    $this->addTeamField($form, $league);
                    $this->addPlayerField($form, $team);
                    $form->get('team')->setdata($league);
                    $form->get('players')->setdata($team);
                } else {
                    $this->addTeamField($form, null);
                    $this->addPlayerField($form, null);
                }
            });
    }

    private function addTeamField(FormInterface $form, ?Week $week) : void{
        $league =  $week?->getLeagueId();
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'team', 
            EntityType::class,
            null,
            [
                'mapped' => false,
                'class' => Team::class,
                // 'placeholder' => $region ? 'Selectionner le département' : 'Selectionner une région',
                'required' => false,
                'auto_initialize' => false,
                'choices' => $league?->getTeams() ?? [],
                'disabled' => !$week
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                $this->addPlayerField($form->getParent(), $form->getData());
            }
        );
        $form->add($builder->getForm());
    }

    private function addPlayerField(FormInterface $form, ?Team $team): void{
        $form->add('players', EntityType::class, [
            'class' => Player::class,
            'choice_label' => 'id',
            'choices' => $team ? $team->getPlayers() : [],
            'multiple' => true,
        ]);
    }


    public function getBlockPrefix() : string
    {
        return 'question_choice';
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Choice::class,
        ]);
    }
}
