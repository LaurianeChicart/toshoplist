<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlanningRepository")
 */
class Planning
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="plannings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Day", mappedBy="planning", orphanRemoval=true)
     */
    private $day;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ListItems", mappedBy="planning", cascade={"persist", "remove"})
     */
    private $listItems;

    public function __construct()
    {
        $this->day = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Day[]
     */
    public function getDay(): Collection
    {
        return $this->day;
    }

    public function addDay(Day $day): self
    {
        if (!$this->day->contains($day)) {
            $this->day[] = $day;
            $day->setPlanning($this);
        }

        return $this;
    }

    public function removeDay(Day $day): self
    {
        if ($this->day->contains($day)) {
            $this->day->removeElement($day);
            // set the owning side to null (unless already changed)
            if ($day->getPlanning() === $this) {
                $day->setPlanning(null);
            }
        }

        return $this;
    }

    public function getListItems(): ?ListItems
    {
        return $this->listItems;
    }

    public function setListItems(ListItems $listItems): self
    {
        $this->listItems = $listItems;

        // set the owning side of the relation if necessary
        if ($this !== $listItems->getPlanning()) {
            $listItems->setPlanning($this);
        }

        return $this;
    }
}
