<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['pseudo'], message: 'There is already an account with this pseudo')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $pseudo = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Badge $badge = null;

    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $choices;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $ptl_lfb = 0.0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $pt_lf2 = 0.0;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }
    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function setBadge(?Badge $badge): static
    {
        // unset the owning side of the relation if necessary
        if ($badge === null && $this->badge !== null) {
            $this->badge->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($badge !== null && $badge->getUser() !== $this) {
            $badge->setUser($this);
        }

        $this->badge = $badge;
        return $this;
    }

    /**
     * @return Collection<int, Choice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): static
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setUser($this);
        }

        return $this;
    }

    public function removeChoice(Choice $choice): static
    {
        if ($this->choices->removeElement($choice)) {
            // set the owning side to null (unless already changed)
            if ($choice->getUser() === $this) {
                $choice->setUser(null);
            }
        }

        return $this;
    }

    public function getPtlLfb(): float
    {
        return $this->ptl_lfb;
    }

    public function setPtlLfb(float $ptl_lfb): self
    {
        $this->ptl_lfb = $ptl_lfb;
        return $this;
    }

    public function getPtLf2(): float
    {
        return $this->pt_lf2;
    }

    public function setPtLf2(float $pt_lf2): self
    {
        $this->pt_lf2 = $pt_lf2;
        return $this;
    }

    // Fonction pour mettre à jour les points cumulés
    public function updateCumulativePoints(): void
    {
        $this->ptl_lfb = 0.0;
        $this->pt_lf2 = 0.0;

        foreach ($this->choices as $choice) {
            $weekId = $choice->getWeek()->getId();
            $points = $choice->getPoints();

            if ($weekId >= 1 && $weekId <= 22) {
                $this->ptl_lfb += $points;
            } elseif ($weekId >= 23 && $weekId <= 44) {
                $this->pt_lf2 += $points;
            }
        }
    }

}
