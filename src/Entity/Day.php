<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DayRepository")
 */
class Day
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlannedMeal", mappedBy="day", orphanRemoval=true)
     */
    private $plannedMeal;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Planning", inversedBy="day")
     * @ORM\JoinColumn(nullable=false)
     */
    private $planning;

    public function __construct()
    {
        $this->plannedMeal = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|PlannedMeal[]
     */
    public function getPlannedMeal(): Collection
    {
        return $this->plannedMeal;
    }

    public function addPlannedMeal(PlannedMeal $plannedMeal): self
    {
        if (!$this->plannedMeal->contains($plannedMeal)) {
            $this->plannedMeal[] = $plannedMeal;
            $plannedMeal->setDay($this);
        }

        return $this;
    }

    public function removePlannedMeal(PlannedMeal $plannedMeal): self
    {
        if ($this->plannedMeal->contains($plannedMeal)) {
            $this->plannedMeal->removeElement($plannedMeal);
            // set the owning side to null (unless already changed)
            if ($plannedMeal->getDay() === $this) {
                $plannedMeal->setDay(null);
            }
        }

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;

        return $this;
    }
}
