<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?float $rate = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    private ?team $team = null;

    #[ORM\ManyToMany(targetEntity: choice::class, inversedBy: 'players')]
    private Collection $choice;

    public function __construct()
    {
        $this->choice = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForename(): ?string
    {
        return $this->forename;
    }

    public function setForename(?string $forename): static
    {
        $this->forename = $forename;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getTeam(): ?team
    {
        return $this->team;
    }

    public function setTeam(?team $team): static
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection<int, choice>
     */
    public function getChoice(): Collection
    {
        return $this->choice;
    }

    public function addChoice(choice $choice): static
    {
        if (!$this->choice->contains($choice)) {
            $this->choice->add($choice);
        }

        return $this;
    }

    public function removeChoice(choice $choice): static
    {
        $this->choice->removeElement($choice);

        return $this;
    }
}
