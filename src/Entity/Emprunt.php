<?php

namespace App\Entity;

use App\Repository\EmpruntRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EmpruntRepository::class)]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['emprunt:read', 'adherent:read'])]
    private ?int $idEmp = null;

    #[ORM\Column]
    #[Groups(['emprunt:read', 'adherent:read'])]
    private ?\DateTime $dateEmprunt = null;

    #[ORM\Column]
    #[Groups(['emprunt:read', 'adherent:read'])]
    private ?\DateTime $dateRetour = null;

    #[ORM\ManyToOne(inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_adh')]
    private ?Adherent $adherent = null;

    #[ORM\ManyToOne(inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_livre')]
    #[Groups(['emprunt:read', 'adherent:read'])]
    private ?Livre $livre = null;

    // Représente les livres sélectionnés pour le formulaire EasyAdmin (sélection multiple)
    private ?array $livresEmpruntes = [];

    public function getLivresEmpruntes(): ?array
    {
        return $this->livresEmpruntes;
    }

    public function setLivresEmpruntes($livres): self
    {
        if ($livres instanceof \Doctrine\Common\Collections\Collection) {
            $this->livresEmpruntes = $livres->toArray();
        } else {
            $this->livresEmpruntes = $livres;
        }
        return $this;
    }

    public function getIdEmp(): ?int
    {
        return $this->idEmp;
    }

    public function getDateEmprunt(): ?\DateTime
    {
        return $this->dateEmprunt;
    }

    public function setDateEmprunt(\DateTime $dateEmprunt): static
    {
        $this->dateEmprunt = $dateEmprunt;

        return $this;
    }

    public function getDateRetour(): ?\DateTime
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTime $dateRetour): static
    {
        $this->dateRetour = $dateRetour;

        return $this;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): static
    {
        $this->adherent = $adherent;

        return $this;
    }

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(?Livre $livre): static
    {
        $this->livre = $livre;

        return $this;
    }
}
