<?php

namespace App\Entity;

use App\Entity\PlayerRate;
use App\Repository\ChoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChoiceRepository::class)]
class Choice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Week::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Week $week = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'choices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $points = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeek(): ?Week
    {
        return $this->week;
    }

    public function setWeek(?Week $week): static
    {
        $this->week = $week;
        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPoints(): ?float
    {
        return $this->points;
    }

    public function setPoints(?float $points): static
    {
        $this->points = $points;
        return $this;
    }
   

    public function updatePoints(EntityManagerInterface $entityManager): void
    {
        $playerRate = $entityManager->getRepository(PlayerRate::class)->findOneBy([
            'player' => $this->getPlayer(),
            'week' => $this->getWeek(),
        ]);
    
        if ($playerRate) {
            // Log de débogage pour vérifier la valeur du rate
            dump($playerRate->getRate());
    
            $this->points = $playerRate->getRate();
        } else {
            // Log de débogage si aucun PlayerRate n'est trouvé
            dump('No PlayerRate found for player and week');
            
            $this->points = 0;
        }
    }
    
    
}
